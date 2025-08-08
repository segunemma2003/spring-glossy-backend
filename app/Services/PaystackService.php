<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Composer\CaBundle\CaBundle;

class PaystackService
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        $this->publicKey = config('services.paystack.public_key');
        $this->baseUrl = 'https://api.paystack.co';
    }

    public function initializePayment(array $data)
    {
        try {
            $response = $this->makeSecureRequest('POST', '/transaction/initialize', $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paystack payment initialization failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'status' => false,
                'message' => 'Payment initialization failed: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Paystack payment initialization exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment initialization failed due to connection error'
            ];
        }
    }

    public function verifyPayment(string $reference)
    {
        try {
            $response = $this->makeSecureRequest('GET', "/transaction/verify/{$reference}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paystack payment verification failed', [
                'reference' => $reference,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'status' => false,
                'message' => 'Payment verification failed: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Paystack payment verification exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment verification failed due to connection error'
            ];
        }
    }

    /**
     * Make a secure HTTP request to Paystack API
     */
    private function makeSecureRequest(string $method, string $endpoint, array $data = [])
    {
        $httpClient = $this->getSecureHttpClient();

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'SpringGlossy/1.0 Laravel',
            ]
        ];

        $url = $this->baseUrl . $endpoint;

        switch (strtoupper($method)) {
            case 'POST':
                return $httpClient->post($url, $data);
            case 'GET':
                return $httpClient->get($url);
            case 'PUT':
                return $httpClient->put($url, $data);
            case 'DELETE':
                return $httpClient->delete($url);
            default:
                throw new \InvalidArgumentException("Unsupported HTTP method: {$method}");
        }
    }

    /**
     * Get HTTP client with proper SSL configuration
     */
    private function getSecureHttpClient()
    {
        try {
            // Get CA bundle path with multiple fallbacks
            $caBundlePath = null;

            if (class_exists(CaBundle::class)) {
                try {
                    $caBundlePath = CaBundle::getBundledCaBundlePath();
                } catch (\Exception $e) {
                    $caBundlePath = CaBundle::getSystemCaRootBundlePath();
                }
            }

            // Fallback to environment variables
            if (!$caBundlePath && env('CURL_CA_BUNDLE')) {
                $caBundlePath = env('CURL_CA_BUNDLE');
            }

            if (!$caBundlePath && env('SSL_CERT_FILE')) {
                $caBundlePath = env('SSL_CERT_FILE');
            }

            // Configure SSL verification
            $verify = env('HTTP_VERIFY_SSL', true);
            if ($verify && $caBundlePath && file_exists($caBundlePath)) {
                $verify = $caBundlePath;
            }

            $options = [
                'verify' => $verify,
                'timeout' => env('HTTP_TIMEOUT', 30),
                'connect_timeout' => env('HTTP_CONNECT_TIMEOUT', 10),
                'http_errors' => false,
                'allow_redirects' => [
                    'max' => 5,
                    'strict' => false,
                    'referer' => true,
                    'protocols' => ['http', 'https'],
                ],
                'headers' => [
                    'User-Agent' => 'Spring-Glossy-Cosmetics/1.0',
                    'Accept' => 'application/json, text/plain, */*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'gzip, deflate, br',
                ],
            ];

            $httpClient = Http::withOptions($options);

            // Add retry logic
            return $httpClient->retry(
                env('HTTP_RETRY_MAX_ATTEMPTS', 3),
                env('HTTP_RETRY_DELAY', 1000),
                function ($exception, $request) {
                    // Retry on connection exceptions and server errors
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException ||
                           ($exception instanceof \Illuminate\Http\Client\RequestException &&
                            $exception->response && $exception->response->status() >= 500) ||
                           in_array($exception->getCode(), [408, 429, 500, 502, 503, 504]);
                }
            );

        } catch (\Exception $e) {
            Log::warning('Paystack SSL configuration fallback: ' . $e->getMessage());

            // Fallback configuration without SSL verification
            return Http::withOptions([
                'verify' => false,
                'timeout' => 30,
                'connect_timeout' => 10,
                'http_errors' => false,
            ])->retry(3, 1000);
        }
    }

    /**
     * Get list of supported banks
     */
    public function getBanks()
    {
        try {
            $response = $this->makeSecureRequest('GET', '/bank');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => false,
                'message' => 'Failed to fetch banks'
            ];

        } catch (\Exception $e) {
            Log::error('Paystack get banks failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to fetch banks due to connection error'
            ];
        }
    }

    /**
     * Test connection to Paystack API
     */
    public function testConnection()
    {
        try {
            $response = $this->makeSecureRequest('GET', '/bank');

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'message' => $response->successful()
                    ? 'Connection successful'
                    : 'Connection failed: ' . $response->body()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get public key for frontend
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature)
    {
        $expectedSignature = hash_hmac('sha512', $payload, $this->secretKey);
        return hash_equals($expectedSignature, $signature);
    }
}
