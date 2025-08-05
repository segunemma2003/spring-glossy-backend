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

        // TEMPORARY: Disable SSL verification for debugging - REMOVE AFTER DEBUGGING
        Http::globalOptions([
            'verify' => false
        ]);

        // Configure SSL certificate verification for HTTP requests
        if (class_exists(CaBundle::class)) {
            config([
                'http.client.options.verify' => CaBundle::getSystemCaRootBundlePath(),
                'http.client.options.timeout' => 30,
                'http.client.options.connect_timeout' => 10,
            ]);
        }

        // Debug HTTP requests to catch SSL errors from Filament
        Http::macro('debug', function () {
            return Http::withOptions([
                'debug' => true,
                'verify' => false // Temporarily for debugging only
            ]);
        });

        // Configure secure HTTP client for Filament
        Http::macro('secure', function () {
            return Http::withOptions([
                'verify' => CaBundle::getBundledCaBundlePath(),
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);
        });

        // Log all HTTP request failures for debugging
        Http::beforeSending(function ($request, $options) {
            Log::info('HTTP Request', [
                'url' => $request->url(),
                'method' => $request->method(),
                'verify' => $options['verify'] ?? 'default',
            ]);
        });

        Http::afterSending(function ($request, $response) {
            if (!$response->successful()) {
                Log::error('HTTP Request Failed', [
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        });
    }
}
