<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModerator
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->activo && $request->user()->tieneTipo('moderador'), 403);

        return $next($request);
    }
}
