<?php

namespace App\Http\Controllers;

use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $users = User::with('tipos_usuario')->get();

        return view('dashboard', compact('users'));
    }
}