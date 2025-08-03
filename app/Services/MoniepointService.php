<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoniepointService
{
    private string $secretKey;
    private string $publicKey;
    private string $merchantId;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.moniepoint.secret_key');
        $this->publicKey = config('services.moniepoint.public_key');
        $this->merchantId = config('services.moniepoint.merchant_id');
        $this->baseUrl = 'https://api.moniepoint.com';
    }

    public function initializePayment(array $data)
    {
        try {
            $payload = [
                'amount' => $data['amount'],
                'currency' => 'NGN',
                'reference' => $data['reference'],
                'description' => $data['description'] ?? 'Payment for order ' . $data['reference'],
                'callback_url' => $data['callback_url'],
                'return_url' => $data['return_url'] ?? $data['callback_url'],
                'customer' => [
                    'email' => $data['email'],
                    'name' => $data['customer_name'] ?? 'Customer',
                ],
                'metadata' => $data['metadata'] ?? [],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'X-Merchant-ID' => $this->merchantId,
            ])->post($this->baseUrl . '/v1/transactions/initiate', $payload);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Moniepoint payment initialization failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment initialization failed'
            ];
        }
    }

    public function verifyPayment(string $reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'X-Merchant-ID' => $this->merchantId,
            ])->get($this->baseUrl . '/v1/transactions/verify/' . $reference);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Moniepoint payment verification failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment verification failed'
            ];
        }
    }

    public function verifyWebhookSignature($payload, $signature)
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->secretKey);
        return hash_equals($expectedSignature, $signature);
    }
}
