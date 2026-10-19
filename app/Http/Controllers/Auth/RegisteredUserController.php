<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UsuarioRolService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, UsuarioRolService $roles): RedirectResponse
    {
        $data = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'tipo_registro' => ['sometimes', 'in:comprador'],
        ]);

        try {
            $user = DB::transaction(function () use ($data, $roles) {
                $user = User::create([
                    'name' => trim($data['nombre_completo']),
                    'nombre_completo' => trim($data['nombre_completo']),
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'fecha_registro' => now(),
                    'activo' => true,
                ]);
                $roles->asignarRolesRegistro($user, 'comprador');

                return $user;
            });
        } catch (RuntimeException $exception) {
            report($exception);
            throw ValidationException::withMessages([
                'nombre_completo' => 'No fue posible completar el registro. Intenta nuevamente.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        try {
            event(new Registered($user));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('verification.notice')->with('status', 'verification-send-failed');
        }

        return redirect()->route('verification.notice');
    }
}
