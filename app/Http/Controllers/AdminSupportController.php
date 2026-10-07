<?php

namespace App\Http\Controllers;

use App\Models\SolicitudSoporte;
use App\Notifications\BuyerActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminSupportController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['estado' => ['nullable', 'in:abierta,en_proceso,cerrada'], 'q' => ['nullable', 'string', 'max:100']]);
        $consultas = SolicitudSoporte::with('usuario')->withCount('respuestas')
            ->when($data['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->when($data['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query->where('asunto', 'like', '%'.$q.'%')->orWhere('mensaje', 'like', '%'.$q.'%')->orWhereHas('usuario', fn ($query) => $query->where('email', 'like', '%'.$q.'%')->orWhere('nombre_completo', 'like', '%'.$q.'%')->orWhere('name', 'like', '%'.$q.'%'))))
            ->latest('updated_at')->latest('id')->paginate(15)->withQueryString();
        $conteos = SolicitudSoporte::selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');

        return view('administrador.consultas.index', compact('consultas', 'conteos'));
    }

    public function show(SolicitudSoporte $solicitud)
    {
        $solicitud->load('usuario');
        $respuestas = $solicitud->respuestas()->with('usuario')->reorder('id', 'desc')->paginate(20);

        return view('administrador.consultas.show', compact('solicitud', 'respuestas'));
    }

    public function reply(Request $request, SolicitudSoporte $solicitud)
    {
        $data = $request->validate(['mensaje' => ['required', 'string', 'min:5', 'max:4000'], 'estado' => ['required', 'in:en_proceso,cerrada']], [
            'mensaje.required' => 'Escribe una respuesta para el usuario.', 'mensaje.min' => 'La respuesta debe contener al menos 5 caracteres.', 'mensaje.max' => 'La respuesta debe contener como máximo 4000 caracteres.',
        ]);
        DB::transaction(function () use ($request, $solicitud, $data) {
            $ticket = SolicitudSoporte::lockForUpdate()->findOrFail($solicitud->id);
            $ticket->respuestas()->create(['id_usuario' => $request->user()->id, 'es_administrador' => true, 'mensaje' => $data['mensaje']]);
            $ticket->update(['estado' => $data['estado']]);
            $ticket->usuario->notify(new BuyerActivityNotification('Respuesta a tu consulta #'.$ticket->id, Str::limit($data['mensaje'], 220), route('comprador.soporte.show', $ticket)));
        });

        $request->attributes->set('staff_activity', ['accion' => 'Respondió una consulta', 'descripcion' => 'Respondió la consulta #'.$solicitud->id.' y la marcó '.($data['estado'] === 'cerrada' ? 'como resuelta.' : 'en proceso.'), 'tipo_objeto' => 'solicitud_soporte', 'id_objeto' => $solicitud->id]);

        return redirect()->route('admin.consultas.show', $solicitud)->with('success', 'Respuesta enviada. El usuario recibió una notificación en su cuenta.');
    }
}
