<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

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
     * Fechas en el idioma de la aplicación ("lunes 3 de noviembre") en las
     * vistas y los correos de citas.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
    }
}
