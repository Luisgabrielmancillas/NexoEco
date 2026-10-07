<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitudVendedorRequest;
use App\Services\SellerRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class SolicitudVendedorController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('vendedor.register');
    }

    public function store(StoreSolicitudVendedorRequest $request, SellerRegistrationService $registration): RedirectResponse
    {
        try {
            $registration->submit($request->user(), $request->validated());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput($request->except(['password', 'password_confirmation']))->withErrors([
                'documentos' => 'No fue posible enviar tu solicitud. Intenta nuevamente.',
            ]);
        }

        if (! $request->user()->hasVerifiedEmail()) {
            try {
                $request->user()->sendEmailVerificationNotification();
            } catch (Throwable $exception) {
                report($exception);

                return redirect()->route('vendedor.register')->with('status', 'verification-send-failed');
            }
        }

        return redirect()->route('vendedor.register')->with('status', 'Solicitud enviada correctamente.');
    }
}
