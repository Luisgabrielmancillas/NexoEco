<?php

namespace App\Http\Controllers;

use App\Models\Opinion;
use App\Models\Producto;
use App\Models\ReporteContenido;
use Illuminate\Http\Request;

class ContentReportController extends Controller
{
    public function store(Request $request, string $tipo, int $id)
    {
        $model = ($tipo === 'productos' ? Producto::publicados() : Opinion::publicadas())->findOrFail($id);
        $data = $request->validate([
            'categoria' => ['required', 'in:contenido_inapropiado,informacion_falsa,spam,otro'],
            'motivo' => ['required', 'string', 'min:10', 'max:2000'],
        ], ['motivo.min' => 'Describe lo ocurrido con al menos 10 caracteres.']);
        ReporteContenido::firstOrCreate(['id_usuario' => $request->user()->id, 'tipo' => $tipo, 'id_contenido' => $model->getKey()], $data);

        return back()->with('status', 'Tu reporte se envió al equipo de moderación. Podrás recibir la respuesta en tus notificaciones.');
    }
}
