<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountArea
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if (! $user->tieneTipo('administrador')) {
            if ($user->tieneTipo('moderador') && $request->routeIs('marketplace.*', 'comprador.*', 'vendedor.*', 'vender', 'productos.show', 'tiendas.show', 'profile.edit')) {
                abort_unless($request->isMethod('GET') || $request->isMethod('HEAD'), 403);
                abort_if($request->expectsJson(), 403);

                return redirect()->route($user->hasVerifiedEmail() ? ($request->routeIs('profile.edit') ? 'moderador.configuracion' : 'moderador.dashboard') : 'verification.notice');
            }

            return $next($request);
        }

        // An administrator keeps the administrative workspace, even with other roles.
        if ($request->routeIs('marketplace.index', 'comprador.*', 'vendedor.*', 'moderador.*', 'vender', 'productos.show', 'tiendas.show')) {
            abort_unless($request->isMethod('GET') || $request->isMethod('HEAD'), 403);
            abort_if($request->expectsJson(), 403);

            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            if ($request->routeIs('productos.show')) {
                return redirect()->route('admin.productos.show', $request->route('producto'));
            }
            if ($request->routeIs('tiendas.show')) {
                return redirect()->route('admin.tiendas.show', $request->route('tienda'));
            }

            return redirect()->route('administrador.dashboard');
        }

        return $next($request);
    }
}
