<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaytechService
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $env;

    public function __construct()
    {
        $this->apiKey    = config('services.paytech.api_key');
        $this->apiSecret = config('services.paytech.api_secret');
        $this->env       = config('services.paytech.env', 'test');
    }

    /**
     * Initie une demande de paiement Paytech.
     *
     * @param  array{
     *   item_name: string,
     *   item_price: int,
     *   ref_command: string,
     *   command_name: string,
     *   ipn_url: string,
     *   success_url: string,
     *   cancel_url: string,
     *   custom_field?: string,
     * } $params
     * @return array{success: int, token?: string, redirect_url?: string, error?: string}
     */
    public function requestPayment(array $params): array
    {
        $payload = array_merge($params, [
            'currency' => 'XOF',
            'env'      => $this->env,
        ]);

        try {
            $response = Http::withHeaders([
                'API_KEY'    => $this->apiKey,
                'API_SECRET' => $this->apiSecret,
            ])->post('https://paytech.sn/api/payment/request-payment', $payload);

            return $response->json() ?? ['success' => 0, 'error' => 'Réponse vide'];
        } catch (\Throwable $e) {
            Log::error('PaytechService::requestPayment erreur', ['error' => $e->getMessage()]);

            return ['success' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifie l'authenticité d'une notification IPN Paytech.
     * Paytech envoie api_key_sha256 et api_secret_sha256 dans le payload.
     */
    public function verifyIpn(array $data): bool
    {
        $expectedKeyHash    = hash('sha256', $this->apiKey);
        $expectedSecretHash = hash('sha256', $this->apiSecret);

        return isset($data['api_key_sha256'], $data['api_secret_sha256'])
            && hash_equals($expectedKeyHash, $data['api_key_sha256'])
            && hash_equals($expectedSecretHash, $data['api_secret_sha256']);
    }

    public function getEnv(): string
    {
        return $this->env;
    }
}
