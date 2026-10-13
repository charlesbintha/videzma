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

        if (! in_array($serviceRequest->payment_method, ['orange_money', 'wave'])) {
            return response()->json(['message' => 'Paiement en espèces, aucune action en ligne requise.'], 422);
        }

        $refCommand = 'SR-'.$serviceRequest->id.'-'.(string) \Illuminate\Support\Str::uuid();

        $result = $this->paytech->requestPayment([
            'item_name' => 'Service de vidange Videzma',
            'item_price' => (int) $serviceRequest->price_amount,
            'ref_command' => $refCommand,
            'command_name' => 'Vidange - '.$serviceRequest->address,
            'ipn_url' => url('/api/payments/ipn'),
            'success_url' => url('/payment/success?ref='.$refCommand.'&type=service&id='.$serviceRequest->id),
            'cancel_url' => url('/payment/cancel?ref='.$refCommand.'&type=service&id='.$serviceRequest->id),
            'custom_field' => json_encode([
                'type' => 'service_request',
                'service_request_id' => $serviceRequest->id,
                'ref_command' => $refCommand,
            ]),
        ]);

        if (empty($result['success']) || $result['success'] != 1) {
            Log::error('Paytech initiateService échoué', ['result' => $result, 'service_request_id' => $serviceRequest->id]);

            return response()->json(['message' => 'Impossible d\'initier le paiement. Veuillez réessayer.'], 500);
        }

        $serviceRequest->update([
            'payment_reference' => $refCommand,
            'payment_token' => $result['token'],
        ]);

        return response()->json([
            'redirect_url' => $result['redirect_url'],
            'token' => $result['token'],
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
        $refCommand = 'SUB-'.$subscription->id.'-'.(string) \Illuminate\Support\Str::uuid();

        $result = $this->paytech->requestPayment([
            'item_name' => 'Forfait Videzma - '.($subscription->plan->name ?? 'Abonnement'),
            'item_price' => (int) $subscription->price_amount,
            'ref_command' => $refCommand,
            'command_name' => 'Souscription forfait - '.($subscription->plan->name ?? ''),
            'ipn_url' => url('/api/payments/ipn'),
            'success_url' => url('/payment/success?ref='.$refCommand.'&type=subscription&id='.$subscription->id),
            'cancel_url' => url('/payment/cancel?ref='.$refCommand.'&type=subscription&id='.$subscription->id),
            'custom_field' => json_encode([
                'type' => 'subscription',
                'subscription_id' => $subscription->id,
                'ref_command' => $refCommand,
            ]),
        ]);

        if (empty($result['success']) || $result['success'] != 1) {
            Log::error('Paytech initiateSubscription échoué', ['result' => $result, 'subscription_id' => $subscription->id]);

            return response()->json(['message' => 'Impossible d\'initier le paiement. Veuillez réessayer.'], 500);
        }

        $subscription->update([
            'payment_reference' => $refCommand,
            'payment_token' => $result['token'],
        ]);

        return response()->json([
            'redirect_url' => $result['redirect_url'],
            'token' => $result['token'],
        ]);
    }

    /**
     * Webhook IPN Paytech — appelé directement par les serveurs Paytech.
     * Route publique (pas d'auth Sanctum).
     */
    public function ipn(Request $request)
    {
        if (! $this->paytech->verifyIpn($request->all())) {
            Log::warning('Paytech IPN: signature invalide');

            return response()->json(['message' => 'Signature invalide.'], 403);
        }

        $validated = $request->validate([
            'type_event' => ['required', 'in:sale_complete,sale_canceled'],
            'ref_command' => ['required', 'string'],
            'custom_field' => ['required', 'json'],
        ]);
        $custom = json_decode($validated['custom_field'], true);
        $model = match ($custom['type'] ?? null) {
            'service_request' => ServiceRequest::class,
            'subscription' => ClientSubscription::class,
            default => null,
        };
        if (! $model) {
            return response()->json(['message' => 'Type de paiement inconnu.'], 422);
        }
        $id = $model === ServiceRequest::class
            ? ($custom['service_request_id'] ?? null)
            : ($custom['subscription_id'] ?? null);
        if (! is_scalar($id) || ! ctype_digit((string) $id) || (int) $id < 1) {
            return response()->json(['message' => 'Identifiant de paiement invalide.'], 422);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($model, $id, $validated) {
            $payment = $model::lockForUpdate()->findOrFail($id);
            if (! $payment->payment_reference || ! hash_equals($payment->payment_reference, $validated['ref_command'])) {
                return response()->json(['message' => 'Référence de paiement obsolète ou invalide.'], 409);
            }
            // Repeated or late cancellation notifications cannot undo a confirmed payment.
            if ($payment->payment_status === 'paid') {
                return response()->json(['message' => 'OK']);
            }
            if ($validated['type_event'] === 'sale_complete') {
                $payment->payment_status = 'paid';
                $payment->paid_at = now();
            } else {
                $payment->payment_status = 'failed';
            }
            $payment->save();

            return response()->json(['message' => 'OK']);
        });
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
            'paid_at' => $serviceRequest->paid_at?->toIso8601String(),
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
            'paid_at' => $subscription->paid_at?->toIso8601String(),
        ]);
    }
}
