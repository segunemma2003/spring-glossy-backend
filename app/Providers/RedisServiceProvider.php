<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Redis;
use Predis\Client;

class RedisServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRedisForHeroku();
    }

    /**
     * Configure Redis for Heroku's self-signed certificates
     */
    private function configureRedisForHeroku(): void
    {
        // Only configure for production environment with Redis URL
        if (config('app.env') === 'production' && env('REDIS_URL')) {
            $this->configureHerokuRedis();
        }
    }

    /**
     * Configure Redis specifically for Heroku's environment
     */
    private function configureHerokuRedis(): void
    {
        try {
            $redisUrl = env('REDIS_URL');

            if (!$redisUrl) {
                return;
            }

            // Parse the Redis URL
            $parsedUrl = parse_url($redisUrl);

            if (!$parsedUrl) {
                \Log::warning('Failed to parse REDIS_URL');
                return;
            }

            // Determine if this is a TLS connection
            $isTls = in_array($parsedUrl['scheme'] ?? '', ['rediss', 'tls']);

            if ($isTls) {
                // Configure Predis options for Heroku's self-signed certificates
                $predisOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                        'cafile' => null,
                    ],
                ];

                // Update the Redis configuration
                config([
                    'database.redis.options.ssl' => $predisOptions['ssl'],
                    'database.redis.default.scheme' => 'tls',
                    'database.redis.cache.scheme' => 'tls',
                ]);

                \Log::info('Redis configured for Heroku TLS with self-signed certificate support');
            }

        } catch (\Exception $e) {
            \Log::error('Failed to configure Redis for Heroku: ' . $e->getMessage());
        }
    }

    /**
     * Create a custom Redis connection for Heroku
     */
    public static function createHerokuRedisConnection(): ?Client
    {
        try {
            $redisUrl = env('REDIS_URL');

            if (!$redisUrl) {
                return null;
            }

            $parsedUrl = parse_url($redisUrl);

            if (!$parsedUrl) {
                return null;
            }

            $connectionParams = [
                'scheme' => str_replace('rediss', 'tls', $parsedUrl['scheme'] ?? 'tcp'),
                'host' => $parsedUrl['host'] ?? '127.0.0.1',
                'port' => $parsedUrl['port'] ?? 6379,
                'password' => $parsedUrl['pass'] ?? null,
                'database' => 0,
            ];

            $options = [];

            // If using TLS, configure SSL options for self-signed certificates
            if (in_array($parsedUrl['scheme'] ?? '', ['rediss', 'tls'])) {
                $options['ssl'] = [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ];
            }

            return new Client($connectionParams, $options);

        } catch (\Exception $e) {
            \Log::error('Failed to create Heroku Redis connection: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Test Redis connection with detailed error reporting
     */
    public static function testRedisConnection(): array
    {
        try {
            // Test using Laravel's Redis facade
            $result = Redis::ping();

            return [
                'success' => true,
                'method' => 'Laravel Redis Facade',
                'result' => $result,
                'message' => 'Redis connection successful'
            ];

        } catch (\Exception $e) {
            // Try with custom Heroku connection
            try {
                $client = self::createHerokuRedisConnection();

                if ($client) {
                    $result = $client->ping();

                    return [
                        'success' => true,
                        'method' => 'Custom Heroku Client',
                        'result' => $result,
                        'message' => 'Redis connection successful with custom client'
                    ];
                }

            } catch (\Exception $customException) {
                return [
                    'success' => false,
                    'method' => 'All methods failed',
                    'laravel_error' => $e->getMessage(),
                    'custom_error' => $customException->getMessage(),
                    'message' => 'Redis connection failed with both methods'
                ];
            }

            return [
                'success' => false,
                'method' => 'Laravel Redis Facade only',
                'error' => $e->getMessage(),
                'message' => 'Redis connection failed'
            ];
        }
    }
}
