<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TiposUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $users = User::with('tipos_usuario')
            ->orderBy('id', 'desc')
            ->get();

        return view(
            'administrador.dashboard',
            compact('users')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        // CREAR USUARIO
        $user = User::create([

            'name' => $request->name,

            'nombre_completo' => $request->name,

            'email' => $request->email,

            // PASSWORD ENCRIPTADO
            'password' => bcrypt($request->password),

            // VERIFICADO AUTOMATICAMENTE
            'email_verified_at' => now(),

            // FECHA REGISTRO
            'fecha_registro' => now(),

            // ACTIVO
            'activo' => true

        ]);

        /*
        |--------------------------------------------------------------------------
        | ASIGNAR ROL ADMINISTRADOR
        |--------------------------------------------------------------------------
        |
        | id_tipo_usuario = 3
        |
        */

        $user->tipos_usuario()->attach(3);

        return back()->with(
            'success',
            'Administrador creado correctamente'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id
        ]);

        $user->update([

            'name' => $request->name,

            'nombre_completo' => $request->name,

            'email' => $request->email,

        ]);

        return back()->with(
            'success',
            'Usuario actualizado correctamente'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVAR / DESACTIVAR
    |--------------------------------------------------------------------------
    */

    public function destroy(User $user)
    {
        // NO DESACTIVARSE A SI MISMO
        if ($user->id === Auth::id()) {

            return back()->with(
                'error',
                'No puedes desactivar tu propia cuenta'
            );
        }

        // CAMBIAR ESTADO
        $user->update([
            'activo' => !$user->activo
        ]);

        // MENSAJE DINAMICO
        if($user->activo)
        {
            return back()->with(
                'success',
                'Usuario reactivado correctamente'
            );
        }

        return back()->with(
            'success',
            'Usuario desactivado correctamente'
        );
    }
}