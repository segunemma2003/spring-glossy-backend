<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonnifyService
{
    private string $secretKey;
    private string $publicKey;
    private string $merchantId;
    private string $contractCode;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.monnify.secret_key');
        $this->publicKey = config('services.monnify.public_key');
        $this->merchantId = config('services.monnify.merchant_id');
        $this->contractCode = config('services.monnify.contract_code');
        $this->baseUrl = 'https://sandbox-api.monnify.com'; // Change to https://api.monnify.com for production
    }

    public function initializePayment(array $data)
    {
        try {
            $payload = [
                'amount' => $data['amount'],
                'customerName' => $data['customer_name'] ?? 'Customer',
                'customerEmail' => $data['email'],
                'paymentReference' => $data['reference'],
                'paymentDescription' => $data['description'] ?? 'Payment for order ' . $data['reference'],
                'currencyCode' => 'NGN',
                'contractCode' => $this->contractCode,
                'redirectUrl' => $data['callback_url'],
                'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER', 'USSD'],
                'metadata' => $data['metadata'] ?? [],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/v1/merchant/transactions/init-transaction', $payload);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Monnify payment initialization failed: ' . $e->getMessage());
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
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ])->get($this->baseUrl . '/api/v2/transactions/' . $reference);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Monnify payment verification failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment verification failed'
            ];
        }
    }

    private function getAccessToken()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->publicKey . ':' . $this->secretKey),
            ])->post($this->baseUrl . '/api/v1/merchant/transactions/query');

            $data = $response->json();
            return $data['responseBody']['accessToken'] ?? null;
        } catch (\Exception $e) {
            Log::error('Monnify access token generation failed: ' . $e->getMessage());
            return null;
        }
    }

    public function verifyWebhookSignature($payload, $signature)
    {
        $expectedSignature = hash_hmac('sha512', $payload, $this->secretKey);
        return hash_equals($expectedSignature, $signature);
    }
}
