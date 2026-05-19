<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Importamos esta herramienta

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
        // FORZAR HTTPS EN PRODUCCIÓN (Railway)
        // Si la variable APP_ENV en Railway dice 'production', Laravel hará todos los enlaces seguros.
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }
}