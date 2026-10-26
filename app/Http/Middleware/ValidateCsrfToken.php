<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

class ValidateCsrfToken extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken
{
    public function handle($request, Closure $next): Response
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            // Return within the web pipeline so StartSession persists the current
            // token and cookie, including sessions restored by a remember cookie.
            if ($request->routeIs('logout') && ! $request->user()) {
                return redirect('/')->header('Cache-Control', 'no-store, private');
            }

            return response()->view('errors.419', [
                'logoutRecovery' => $request->routeIs('logout'),
            ], 419)->header('Cache-Control', 'no-store, private');
        }
    }
}
