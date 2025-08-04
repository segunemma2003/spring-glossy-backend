<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

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

        // Handle Heroku DATABASE_URL for SSL connections
        if (config('app.env') === 'production' && env('DATABASE_URL')) {
            $url = parse_url(env('DATABASE_URL'));

            config([
                'database.connections.pgsql.host' => $url['host'],
                'database.connections.pgsql.port' => $url['port'],
                'database.connections.pgsql.database' => ltrim($url['path'], '/'),
                'database.connections.pgsql.username' => $url['user'],
                'database.connections.pgsql.password' => $url['pass'],
                'database.connections.pgsql.sslmode' => 'require',
            ]);
        }
    }
}
