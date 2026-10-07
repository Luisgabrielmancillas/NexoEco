<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\RegistroActividad;
use App\Models\SolicitudSoporte;
use App\Models\SolicitudVendedor;
use App\Models\Tienda;
use App\Models\User;
use App\Support\AccountRoles;

class Dashboard_Admin_Controller extends Controller
{
    public function index()
    {
        $userCounts = User::selectRaw('COUNT(*) as total, SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos, SUM(CASE WHEN email_verified_at IS NULL THEN 1 ELSE 0 END) as sin_verificar')->first();
        $stats = [
            'usuarios' => (int) $userCounts->total,
            'activos' => (int) $userCounts->activos,
            'inactivos' => (int) $userCounts->total - (int) $userCounts->activos,
            'sin_verificar' => (int) $userCounts->sin_verificar,
            'tiendas' => Tienda::count(),
            'productos' => Producto::count(),
            'consultas' => SolicitudSoporte::where('estado', '!=', 'cerrada')->count(),
            'solicitudes' => SolicitudVendedor::where('estado', SolicitudVendedor::ESTADO_EN_REVISION)->count(),
        ];
        $roles = collect(AccountRoles::LABELS)->map(fn ($label, $role) => ['nombre' => $label, 'total' => User::whereHas('tipos_usuario', fn ($query) => $query->whereRaw('LOWER(nombre_tipo) = ?', [$role]))->count()]);
        $months = collect(range(5, 0))->map(function ($offset) {
            $start = now()->startOfMonth()->subMonths($offset);

            return ['mes' => $start->locale('es')->translatedFormat('M Y'), 'total' => User::whereBetween(\Illuminate\Support\Facades\DB::raw('COALESCE(fecha_registro, created_at)'), [$start, $start->copy()->endOfMonth()])->count()];
        });
        $recentActivities = RegistroActividad::important()->orderByDesc('registrada_en')->orderByDesc('id')->paginate(10, ['*'], 'pagina_actividad')->fragment('acciones-recientes');

        return view('administrador.dashboard', compact('stats', 'roles', 'months', 'recentActivities'));
    }
}
