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
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->secretKey = config('monnify.secret_key');
        $this->publicKey = config('monnify.public_key');
        $this->merchantId = config('monnify.merchant_id');
        $this->contractCode = config('monnify.contract_code');
        $this->baseUrl = config('monnify.base_url');
    }

    /**
     * Initialize a payment transaction
     */
    public function initializePayment(array $data)
    {
        try {
            // Get access token first
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'status' => false,
                    'message' => 'Failed to get access token'
                ];
            }

            $payload = [
                'amount' => $data['amount'],
                'customerName' => $data['customer_name'] ?? 'Customer',
                'customerEmail' => $data['email'],
                'paymentReference' => $data['reference'],
                'paymentDescription' => $data['description'] ?? 'Payment for order ' . $data['reference'],
                'currencyCode' => config('monnify.currency', 'NGN'),
                'contractCode' => $this->contractCode,
                'redirectUrl' => $data['callback_url'],
                'paymentMethods' => config('monnify.payment_methods', ['CARD', 'ACCOUNT_TRANSFER', 'USSD']),
                'metadata' => $data['metadata'] ?? [],
            ];

            $response = Http::timeout(config('monnify.timeout', 30))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/api/v1/merchant/transactions/init-transaction', $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['responseBody'])) {
                return [
                    'status' => true,
                    'data' => [
                        'reference' => $responseData['responseBody']['paymentReference'],
                        'checkout_url' => $responseData['responseBody']['checkoutUrl'],
                        'transaction_reference' => $responseData['responseBody']['transactionReference'],
                    ]
                ];
            }

            Log::error('Monnify payment initialization failed: ' . json_encode($responseData));
            return [
                'status' => false,
                'message' => $responseData['responseMessage'] ?? 'Payment initialization failed'
            ];

        } catch (\Exception $e) {
            Log::error('Monnify payment initialization error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment initialization failed'
            ];
        }
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(string $reference)
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'status' => false,
                    'message' => 'Failed to get access token'
                ];
            }

            $response = Http::timeout(config('monnify.timeout', 30))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->get($this->baseUrl . '/api/v2/transactions/' . $reference);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['responseBody'])) {
                $transaction = $responseData['responseBody'];
                return [
                    'status' => true,
                    'data' => [
                        'status' => $transaction['paymentStatus'] === 'PAID' ? 'success' : 'pending',
                        'amount' => $transaction['paidAmount'],
                        'reference' => $transaction['paymentReference'],
                        'transaction_reference' => $transaction['transactionReference'],
                        'payment_status' => $transaction['paymentStatus'],
                        'paid_on' => $transaction['paidOn'],
                    ]
                ];
            }

            Log::error('Monnify payment verification failed: ' . json_encode($responseData));
            return [
                'status' => false,
                'message' => $responseData['responseMessage'] ?? 'Payment verification failed'
            ];

        } catch (\Exception $e) {
            Log::error('Monnify payment verification error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment verification failed'
            ];
        }
    }

    /**
     * Get access token for API authentication
     */
    private function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $response = Http::timeout(config('monnify.timeout', 30))
                ->withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($this->publicKey . ':' . $this->secretKey),
                ])->post($this->baseUrl . '/api/v1/merchant/transactions/query');

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['responseBody']['accessToken'])) {
                $this->accessToken = $responseData['responseBody']['accessToken'];
                return $this->accessToken;
            }

            Log::error('Monnify access token generation failed: ' . json_encode($responseData));
            return null;

        } catch (\Exception $e) {
            Log::error('Monnify access token error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        try {
            $expectedSignature = hash_hmac('sha512', $payload, $this->secretKey);
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('Monnify webhook signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $reference)
    {
        return $this->verifyPayment($reference);
    }

    /**
     * Get account balance
     */
    public function getAccountBalance()
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'status' => false,
                    'message' => 'Failed to get access token'
                ];
            }

            $response = Http::timeout(config('monnify.timeout', 30))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                ])->get($this->baseUrl . '/api/v1/disbursements/wallet/balance');

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['responseBody'])) {
                return [
                    'status' => true,
                    'data' => $responseData['responseBody']
                ];
            }

            return [
                'status' => false,
                'message' => $responseData['responseMessage'] ?? 'Failed to get balance'
            ];

        } catch (\Exception $e) {
            Log::error('Monnify balance check error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to get balance'
            ];
        }
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods()
    {
        return config('monnify.payment_methods', ['CARD', 'ACCOUNT_TRANSFER', 'USSD']);
    }

    /**
     * Get configuration
     */
    public function getConfig()
    {
        return [
            'environment' => config('monnify.environment'),
            'currency' => config('monnify.currency'),
            'payment_methods' => $this->getPaymentMethods(),
        ];
    }
}
