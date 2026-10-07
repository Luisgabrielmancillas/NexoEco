<?php

namespace App\Http\Controllers;

use App\Models\SolicitudSoporte;
use App\Notifications\BuyerActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BuyerSupportController extends Controller
{
    public function thread(Request $request, SolicitudSoporte $solicitud): View
    {
        abort_unless($solicitud->id_usuario === $request->user()->id, 404);
        $respuestas = $solicitud->respuestas()->with('usuario')->reorder('id', 'desc')->paginate(20);

        return view('comprador.consulta', compact('solicitud', 'respuestas'));
    }

    public function reply(Request $request, SolicitudSoporte $solicitud): RedirectResponse
    {
        abort_unless($solicitud->id_usuario === $request->user()->id, 404);
        $data = $request->validate(['mensaje' => ['required', 'string', 'min:5', 'max:4000']], ['mensaje.required' => 'Escribe tu mensaje.', 'mensaje.min' => 'Escribe al menos 5 caracteres.', 'mensaje.max' => 'El mensaje debe contener como máximo 4000 caracteres.']);
        DB::transaction(function () use ($request, $solicitud, $data) {
            $ticket = SolicitudSoporte::lockForUpdate()->findOrFail($solicitud->id);
            $ticket->respuestas()->create(['id_usuario' => $request->user()->id, 'es_administrador' => false, 'mensaje' => $data['mensaje']]);
            $ticket->update(['estado' => 'abierta']);
        });

        return redirect()->route('comprador.soporte.show', $solicitud)->with('status', 'Tu mensaje fue enviado a soporte.');
    }

    public function show(Request $request, string $seccion): View
    {
        return view('comprador.soporte', [
            'seccion' => $seccion,
            'solicitudes' => $seccion === 'contacto' ? $request->user()->solicitudesSoporte()->withCount('respuestas')->latest('updated_at')->paginate(8) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asunto' => ['required', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'min:10', 'max:4000'],
        ], [
            'asunto.required' => 'Escribe el asunto de tu consulta.',
            'mensaje.required' => 'Cuéntanos en qué necesitas ayuda.',
            'mensaje.min' => 'Describe tu consulta con al menos 10 caracteres.',
            'mensaje.max' => 'Tu consulta debe contener como máximo 4000 caracteres.',
        ]);
        $solicitud = $request->user()->solicitudesSoporte()->create($data);
        $request->user()->notify(new BuyerActivityNotification('Recibimos tu solicitud de soporte', 'Tu consulta #'.$solicitud->id.' quedó registrada: '.$solicitud->asunto, route('comprador.soporte', 'contacto')));

        return redirect()->route('comprador.soporte', 'contacto')->with('status', 'Tu consulta fue registrada correctamente.');
    }
}
