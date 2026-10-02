<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SolicitudVendedor;
use App\Models\User;
use App\Services\UsuarioRolService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }


    public function store(
        Request $request,
        UsuarioRolService $usuarioRolService
    ): RedirectResponse {
        $request->validate([
            'nombre_completo' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],

            'tipo_registro' => [
                'required',
                Rule::in([
                    'comprador',
                    'vendedor',
                ]),
            ],
        ]);


        try {

            $user = DB::transaction(
                function () use (
                    $request,
                    $usuarioRolService
                ) {
                    $nombre = trim(
                        $request->string(
                            'nombre_completo'
                        )->toString()
                    );


                    $user = User::create([
                        'name' => $nombre,

                        'nombre_completo' => $nombre,

                        'email' => mb_strtolower(
                            trim(
                                $request->string(
                                    'email'
                                )->toString()
                            )
                        ),

                        'password' => Hash::make(
                            $request->string(
                                'password'
                            )->toString()
                        ),

                        'fecha_registro' => now(),

                        'activo' => true,
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | SIEMPRE COMPRADOR
                    |--------------------------------------------------------------------------
                    */

                    $usuarioRolService
                        ->asignarRolesRegistro(
                            $user,
                            $request->string(
                                'tipo_registro'
                            )->toString()
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | INTENCIÓN DE VENDER
                    |--------------------------------------------------------------------------
                    |
                    | NO asignamos vendedor.
                    |
                    | Solamente abrimos una solicitud pendiente.
                    |
                    */

                    if (
                        $request->string(
                            'tipo_registro'
                        )->toString()
                        === 'vendedor'
                    ) {
                        SolicitudVendedor::create([
                            'id_usuario' => $user->id,

                            'estado' =>
                                SolicitudVendedor::ESTADO_PENDIENTE_DOCUMENTOS,

                            'fecha_solicitud' => now(),
                        ]);
                    }


                    return $user;
                }
            );

        } catch (RuntimeException $exception) {

            report($exception);


            throw ValidationException::withMessages([
                'tipo_registro' =>
                    'No fue posible completar el registro. Intenta nuevamente.',
            ]);
        }


        event(
            new Registered($user)
        );


        Auth::login($user);


        return redirect()
            ->route('verification.notice');
    }
}