<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // <-- ¡Importante esta línea!

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // Redirección para usuarios que ya tienen sesión activa
        $middleware->redirectUsersTo(fn (Request $request) => 
            $request->user()->role === 'administrador' 
                ? route('administrador.dashboard') 
                : route('comprador.dashboard')
        );

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();