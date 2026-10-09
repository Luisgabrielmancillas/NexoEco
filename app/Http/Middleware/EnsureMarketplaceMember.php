<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMarketplaceMember
{
    public function handle(Request $request, Closure $next)
    {
        abort_if($request->user()->tieneAlgunTipo(['administrador', 'moderador']), 403);

        return $next($request);
    }
}
