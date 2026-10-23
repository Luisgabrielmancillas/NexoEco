<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica si el usuario NO tiene una suscripción activa
        if (!auth()->user()->subscription?->entitled()) {
            return redirect()->route('vendedor.suscripcion.index')
                ->with('error', 'Esta sección es exclusiva de los planes Impulso y Pro. ¡Mejora tu plan para usarla!');
        }

        return $next($request);
    }
}