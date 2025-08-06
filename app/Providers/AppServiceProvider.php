<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;

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
        if (env('CURL_CA_BUNDLE')) {
            config([
                'http.client.options.verify' => env('CURL_CA_BUNDLE'),
                'http.client.options.timeout' => 30,
                'http.client.options.connect_timeout' => 10,
            ]);
        }
    }
}
