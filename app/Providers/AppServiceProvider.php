<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Composer\CaBundle\CaBundle;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Configure SSL certificate verification for HTTP requests
        $this->configureHttpClient();

        // Configure database SSL
        $this->configureDatabaseSSL();

        // Configure Redis SSL
        $this->configureRedisSSL();
    }

    /**
     * Configure HTTP client with SSL certificate verification
     */
    protected function configureHttpClient(): void
    {
        try {
            // Get CA bundle path
            $caBundlePath = null;

            if (class_exists(CaBundle::class)) {
                $caBundlePath = CaBundle::getBundledCaBundlePath();
            } elseif (env('CURL_CA_BUNDLE')) {
                $caBundlePath = env('CURL_CA_BUNDLE');
            } elseif (env('SSL_CERT_FILE')) {
                $caBundlePath = env('SSL_CERT_FILE');
            }

            // Configure HTTP client options
            $options = [
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

            // Configure SSL verification
            if (env('HTTP_VERIFY_SSL', true)) {
                if ($caBundlePath && file_exists($caBundlePath)) {
                    $options['verify'] = $caBundlePath;
                } else {
                    // Fallback to system CA bundle
                    $options['verify'] = true;
                }
            } else {
                $options['verify'] = false;
            }

            // Set global HTTP client options
            Http::withOptions($options);

            // Log successful configuration
            if (config('app.debug')) {
                Log::info('HTTP client configured with SSL verification', [
                    'ca_bundle_path' => $caBundlePath,
                    'verify_ssl' => env('HTTP_VERIFY_SSL', true),
                    'timeout' => env('HTTP_TIMEOUT', 30),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to configure HTTP client SSL', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Configure database SSL settings
     */
    protected function configureDatabaseSSL(): void
    {
        try {
            // Set database SSL mode
            if (env('DB_SSLMODE')) {
                config(['database.connections.pgsql.sslmode' => env('DB_SSLMODE')]);
            }

            // Add SSL options to database configuration
            $sslOptions = [];

            if (env('DB_SSL_CERT')) {
                $sslOptions['sslcert'] = env('DB_SSL_CERT');
            }

            if (env('DB_SSL_KEY')) {
                $sslOptions['sslkey'] = env('DB_SSL_KEY');
            }

            if (env('DB_SSL_ROOT_CERT')) {
                $sslOptions['sslrootcert'] = env('DB_SSL_ROOT_CERT');
            }

            if (!empty($sslOptions)) {
                config(['database.connections.pgsql.options' => $sslOptions]);
            }

            if (config('app.debug')) {
                Log::info('Database SSL configured', [
                    'sslmode' => env('DB_SSLMODE'),
                    'ssl_options' => $sslOptions,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to configure database SSL', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Configure Redis SSL settings
     */
    protected function configureRedisSSL(): void
    {
        try {
            // Set Redis client
            if (env('REDIS_CLIENT')) {
                config(['database.redis.client' => env('REDIS_CLIENT')]);
            }

            // Configure Redis SSL options
            $redisConnections = config('database.redis.connections', []);

            foreach ($redisConnections as $connection => $config) {
                if (env('REDIS_SSL_ENABLED', false)) {
                    $redisConnections[$connection]['scheme'] = 'tls';
                    $redisConnections[$connection]['ssl'] = [
                        'verify_peer' => env('REDIS_SSL_VERIFY_PEER', true),
                        'verify_peer_name' => env('REDIS_SSL_VERIFY_PEER_NAME', true),
                    ];
                }
            }

            config(['database.redis.connections' => $redisConnections]);

            if (config('app.debug')) {
                Log::info('Redis SSL configured', [
                    'client' => env('REDIS_CLIENT'),
                    'ssl_enabled' => env('REDIS_SSL_ENABLED', false),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to configure Redis SSL', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
