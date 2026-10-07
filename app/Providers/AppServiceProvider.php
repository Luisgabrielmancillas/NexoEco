<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Services\BuyerAccountContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer(['layouts.marketplace', 'layouts.comprador.app', 'marketplace.*', 'comprador.*'], function ($view) {
            $view->with(app(\App\Services\BuyerAccountContext::class)->data());
        });
        // Registramos que 'layouts.comprador.app' es un componente basado en tu archivo
        Blade::component('layouts.comprador.comprador-app', 'layouts.comprador.app');
    }
}
