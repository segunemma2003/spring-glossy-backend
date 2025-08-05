<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
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
        if (class_exists(CaBundle::class)) {
            config([
                'http.client.options.verify' => CaBundle::getSystemCaRootBundlePath(),
                'http.client.options.timeout' => 30,
                'http.client.options.connect_timeout' => 10,
            ]);
        }
    }
}
