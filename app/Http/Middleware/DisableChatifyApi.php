<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableChatifyApi
{
    public function handle(Request $request, Closure $next)
    {
        abort(404);
    }
}
