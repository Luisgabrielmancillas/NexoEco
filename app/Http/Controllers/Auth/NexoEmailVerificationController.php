<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SolicitudVendedor;
use App\Services\EmailVerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class NexoEmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($request->user()->tieneTipo('administrador') ? 'administrador.dashboard' : 'comprador.dashboard');
        }
        if (! $request->user()->tieneTipo('administrador') && ($request->user()->solicitudVendedor()->exists() || $request->user()->tieneTipo('vendedor'))) {
            return redirect()->route('vendedor.register');
        }

        return view('auth.verify-email');
    }

    public function verify(Request $request, EmailVerificationCodeService $codes): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasVerifiedEmail()) {
            $data = $request->validate([
                'codigo' => ['required', 'string', 'regex:/\A[0-9]{4}\z/'],
            ], [
                'codigo.required' => 'Ingresa el código que recibiste por correo.',
                'codigo.string' => 'Ingresa un código de 4 dígitos.',
                'codigo.regex' => 'El código debe contener exactamente 4 dígitos.',
            ]);
            $codes->verify($user, $data['codigo']);
        }
        $request->session()->forget('url.intended');
        if ($user->tieneTipo('administrador')) {
            return redirect()->route('administrador.dashboard');
        }
        if ($user->solicitudVendedor()->where('estado', SolicitudVendedor::ESTADO_EN_REVISION)->exists()) {
            return redirect()->route('comprador.dashboard')->with('status', 'Tu cuenta de vendedor está en revisión. Mientras tanto, puedes usar tu cuenta como comprador.');
        }

        return redirect()->route('comprador.dashboard');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($request->user()->tieneTipo('administrador') ? 'administrador.dashboard' : 'comprador.dashboard');
        }
        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('status', 'verification-send-failed');
        }

        return back()->with('status', 'verification-code-sent');
    }
}
