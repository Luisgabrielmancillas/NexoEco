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
        $middleware->alias(['administrator' => \App\Http\Middleware\EnsureAdministrator::class, 'seller' => \App\Http\Middleware\EnsureSeller::class, 'moderator' => \App\Http\Middleware\EnsureModerator::class]);
        $middleware->web(append: [\App\Http\Middleware\EnsureActiveAccount::class, \App\Http\Middleware\EnsureAccountArea::class, \App\Http\Middleware\RecordStaffActivity::class]);

        // Redirección para usuarios que ya tienen sesión activa
        $middleware->redirectUsersTo(function (Request $request) {
            if (! $request->user()->hasVerifiedEmail()) {
                return route('verification.notice');
            }

            return $request->user()->tieneTipo('administrador')
                ? route('administrador.dashboard')
                : route($request->user()->tieneTipo('moderador') ? 'moderador.dashboard' : 'comprador.dashboard');
        });

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
