<?php

namespace App\Http\Controllers;

use App\Models\User;

class Dashboard_Admin_Controller extends Controller
{
    public function index()
    {
        $users = User::with('tipos_usuario')
            ->whereHas('tipos_usuario', function ($query) {
                $query->where('nombre_tipo', 'Administrador');
            })
            ->get();

        return view('administrador.dashboard', compact('users'));
    }
}