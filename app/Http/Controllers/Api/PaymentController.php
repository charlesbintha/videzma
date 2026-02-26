<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientSubscription;
use App\Models\ServiceRequest;
use App\Services\PaytechService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected PaytechService $paytech) {}

    /**
     * Initier le paiement Paytech pour une demande de service.
     * Retourne l'URL de redirection vers le checkout Paytech.
     */
    public function initiateService(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();

        if ($serviceRequest->client_id !== $user->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        if ($serviceRequest->payment_status === 'paid') {
            return response()->json(['message' => 'Cette demande est déjà payée.'], 422);
        }

        if (!in_array($serviceRequest->payment_method, ['orange_money', 'wave'])) {
            return response()->json(['message' => 'Paiement en espèces, aucune action en ligne requise.'], 422);
        }

        $refCommand = 'SR-' . $serviceRequest->id . '-' . time();

        $result = $this->paytech->requestPayment([
            'item_name'    => 'Service de vidange Videzma',
            'item_price'   => (int) $serviceRequest->price_amount,
            'ref_command'  => $refCommand,
            'command_name' => 'Vidange - ' . $serviceRequest->address,
            'ipn_url'      => url('/api/payments/ipn'),
            'success_url'  => url('/payment/success?ref=' . $refCommand . '&type=service&id=' . $serviceRequest->id),
            'cancel_url'   => url('/payment/cancel?ref=' . $refCommand . '&type=service&id=' . $serviceRequest->id),
            'custom_field' => json_encode([
                'type'               => 'service_request',
                'service_request_id' => $serviceRequest->id,
                'ref_command'        => $refCommand,
            ]),
        ]);

        if (empty($result['success']) || $result['success'] != 1) {
            Log::error('Paytech initiateService échoué', ['result' => $result, 'service_request_id' => $serviceRequest->id]);

            return response()->json(['message' => 'Impossible d\'initier le paiement. Veuillez réessayer.'], 500);
        }

        $serviceRequest->update([
            'payment_reference' => $refCommand,
            'payment_token'     => $result['token'],
        ]);

        return response()->json([
            'redirect_url' => $result['redirect_url'],
            'token'        => $result['token'],
        ]);
    }

    /**
     * Initier le paiement Paytech pour une souscription.
     */
    public function initiateSubscription(Request $request, ClientSubscription $subscription)
    {
        $user = $request->user();

        if ($subscription->client_id !== $user->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        if ($subscription->payment_status === 'paid') {
            return response()->json(['message' => 'Cette souscription est déjà payée.'], 422);
        }

        if ($subscription->payment_method === 'cash') {
            return response()->json(['message' => 'Paiement en espèces, aucune action en ligne requise.'], 422);
        }

        $subscription->load('plan');
        $refCommand = 'SUB-' . $subscription->id . '-' . time();

        $result = $this->paytech->requestPayment([
            'item_name'    => 'Forfait Videzma - ' . ($subscription->plan->name ?? 'Abonnement'),
            'item_price'   => (int) $subscription->plan->price,
            'ref_command'  => $refCommand,
            'command_name' => 'Souscription forfait - ' . ($subscription->plan->name ?? ''),
            'ipn_url'      => url('/api/payments/ipn'),
            'success_url'  => url('/payment/success?ref=' . $refCommand . '&type=subscription&id=' . $subscription->id),
            'cancel_url'   => url('/payment/cancel?ref=' . $refCommand . '&type=subscription&id=' . $subscription->id),
            'custom_field' => json_encode([
                'type'            => 'subscription',
                'subscription_id' => $subscription->id,
                'ref_command'     => $refCommand,
            ]),
        ]);

        if (empty($result['success']) || $result['success'] != 1) {
            Log::error('Paytech initiateSubscription échoué', ['result' => $result, 'subscription_id' => $subscription->id]);

            return response()->json(['message' => 'Impossible d\'initier le paiement. Veuillez réessayer.'], 500);
        }

        $subscription->update([
            'payment_reference' => $refCommand,
            'payment_token'     => $result['token'],
        ]);

        return response()->json([
            'redirect_url' => $result['redirect_url'],
            'token'        => $result['token'],
        ]);
    }

    /**
     * Webhook IPN Paytech — appelé directement par les serveurs Paytech.
     * Route publique (pas d'auth Sanctum).
     */
    public function ipn(Request $request)
    {
        if (!$this->paytech->verifyIpn($request->all())) {
            Log::warning('Paytech IPN: signature invalide', ['payload' => $request->all()]);

            return response()->json(['message' => 'Signature invalide.'], 403);
        }

        $typeEvent   = $request->input('type_event');
        $customField = json_decode($request->input('custom_field', '{}'), true);
        $type        = $customField['type'] ?? null;

        Log::info('Paytech IPN reçu', ['type_event' => $typeEvent, 'custom_field' => $customField]);

        if ($type === 'service_request') {
            $serviceRequestId = $customField['service_request_id'] ?? null;
            $serviceRequest   = $serviceRequestId ? ServiceRequest::find($serviceRequestId) : null;

            if (!$serviceRequest) {
                return response()->json(['message' => 'Demande introuvable.'], 404);
            }

            if ($typeEvent === 'sale_complete') {
                $serviceRequest->update([
                    'payment_status' => 'paid',
                    'paid_at'        => now(),
                ]);
            } elseif ($typeEvent === 'sale_canceled') {
                $serviceRequest->update(['payment_status' => 'failed']);
            }
        } elseif ($type === 'subscription') {
            $subscriptionId = $customField['subscription_id'] ?? null;
            $subscription   = $subscriptionId ? ClientSubscription::find($subscriptionId) : null;

            if (!$subscription) {
                return response()->json(['message' => 'Souscription introuvable.'], 404);
            }

            if ($typeEvent === 'sale_complete') {
                $subscription->update([
                    'payment_status' => 'paid',
                    'paid_at'        => now(),
                    'status'         => ClientSubscription::STATUS_ACTIVE,
                ]);
            } elseif ($typeEvent === 'sale_canceled') {
                $subscription->update(['payment_status' => 'failed']);
            }
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Vérifier le statut de paiement d'une demande de service.
     */
    public function statusService(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->client_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json([
            'payment_status' => $serviceRequest->payment_status,
            'paid_at'        => $serviceRequest->paid_at?->toIso8601String(),
        ]);
    }

    /**
     * Vérifier le statut de paiement d'une souscription.
     */
    public function statusSubscription(Request $request, ClientSubscription $subscription)
    {
        if ($subscription->client_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json([
            'payment_status' => $subscription->payment_status,
            'paid_at'        => $subscription->paid_at?->toIso8601String(),
        ]);
    }
}
