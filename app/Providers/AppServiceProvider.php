<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
    // Registramos que 'layouts.comprador.app' es un componente basado en tu archivo
    Blade::component('layouts.comprador.comprador-app', 'layouts.comprador.app');
   }
}
