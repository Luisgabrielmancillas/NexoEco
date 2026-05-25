<?php

namespace App\Http\Controllers;

use App\Models\TiposUsuario;
use Illuminate\Http\Request;

class TipoUsuarioController extends Controller
{
    public function index()
    {
        $tipos = TiposUsuario::orderBy('id_tipo_usuario', 'desc')->get();

        return view('tipos_usuario.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipos_usuario.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_tipo' => 'required|string|max:100|unique:tipos_usuario,nombre_tipo',
        ]);

        TiposUsuario::create([
            'nombre_tipo' => $request->nombre_tipo,
        ]);

        return redirect()
            ->route('tipos-usuario.index')
            ->with('success', 'Tipo de usuario creado correctamente.');
    }

    public function show(TiposUsuario $tiposUsuario)
    {
        return view('tipos_usuario.show', compact('tiposUsuario'));
    }

    public function edit(TiposUsuario $tiposUsuario)
    {
        return view('tipos_usuario.edit', compact('tiposUsuario'));
    }

    public function update(Request $request, TiposUsuario $tiposUsuario)
    {
        $request->validate([
            'nombre_tipo' => 'required|string|max:100|unique:tipos_usuario,nombre_tipo,' . $tiposUsuario->id_tipo_usuario . ',id_tipo_usuario',
        ]);

        $tiposUsuario->update([
            'nombre_tipo' => $request->nombre_tipo,
        ]);

        return redirect()
            ->route('tipos-usuario.index')
            ->with('success', 'Tipo de usuario actualizado correctamente.');
    }

    public function destroy(TiposUsuario $tiposUsuario)
    {
        if ($tiposUsuario->users()->exists()) {
            return redirect()
                ->route('tipos-usuario.index')
                ->with('error', 'No puedes eliminar este tipo porque tiene usuarios asignados.');
        }

        $tiposUsuario->delete();

        return redirect()
            ->route('tipos-usuario.index')
            ->with('success', 'Tipo de usuario eliminado correctamente.');
    }
}