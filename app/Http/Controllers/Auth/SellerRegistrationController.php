<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreSellerRegistrationRequest;
use App\Services\SellerRegistrationService;
use App\Services\UsuarioRolService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SellerRegistrationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if ($user?->hasVerifiedEmail() && $user->tieneTipo('vendedor')) {
            return redirect()->route('vendedor.dashboard');
        }

        return view('auth.seller-register', [
            'user' => $user,
            'solicitud' => $user?->solicitudVendedor()->with('documentos')->first(),
        ]);
    }

    public function store(StoreSellerRegistrationRequest $request, SellerRegistrationService $registration, UsuarioRolService $roles): RedirectResponse
    {
        try {
            $user = $registration->register($request->validated(), $roles);
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages([
                'documentos' => 'No fue posible completar el registro. Intenta nuevamente.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        try {
            event(new Registered($user));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('vendedor.register')->with('status', 'verification-send-failed');
        }

        return redirect()->route('vendedor.register')->with('status', 'verification-code-sent');
    }
}
