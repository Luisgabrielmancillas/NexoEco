<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if ($user->tieneTipo('Administrador')) {
            $request->session()->forget('url.intended');

            return redirect()->route('administrador.dashboard');
        }

        if ($user->tieneTipo('Moderador')) {
            $request->session()->forget('url.intended');

            return redirect()->route('moderador.dashboard');
        }

        if ($user->tieneTipo('Vendedor')) {
            return redirect()->intended(route('comprador.dashboard'));
        }

        if ($user->tieneTipo('Comprador')) {
            return redirect()->intended(route('comprador.dashboard'));
        }

        return redirect()->intended(route('comprador.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
