<?php

namespace App\Http\Controllers;

use App\Models\DocumentoVendedor;
use App\Models\SolicitudVendedor;
use App\Notifications\BuyerActivityNotification;
use App\Support\AccountRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminSellerApplicationController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['estado' => ['nullable', 'in:en_revision,requiere_correccion,aprobada,rechazada,pendiente_documentos']]);
        $solicitudes = SolicitudVendedor::with('usuario')->withCount('documentos')->when($data['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))->orderByDesc('fecha_solicitud')->paginate(15)->withQueryString();

        return view('administrador.solicitudes.index', compact('solicitudes'));
    }

    public function show(SolicitudVendedor $solicitud)
    {
        $solicitud->load(['usuario', 'documentos', 'moderador']);

        return view('administrador.solicitudes.show', compact('solicitud'));
    }

    public function document(SolicitudVendedor $solicitud, DocumentoVendedor $documento)
    {
        abort_unless($documento->id_solicitud === $solicitud->id_solicitud && Storage::disk('local')->exists($documento->ruta_archivo), 404);

        return Storage::disk('local')->download($documento->ruta_archivo, $documento->nombre_original, ['Cache-Control' => 'no-store, private', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function preview(SolicitudVendedor $solicitud, DocumentoVendedor $documento)
    {
        $disk = Storage::disk('local');
        abort_unless($documento->id_solicitud === $solicitud->id_solicitud && $disk->exists($documento->ruta_archivo), 404);
        $mime = $disk->mimeType($documento->ruta_archivo);
        abort_unless(in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true), 415, 'Este formato no admite vista previa.');
        $response = response()->file($disk->path($documento->ruta_archivo), [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "frame-ancestors 'self'",
        ]);
        $response->setContentDisposition('inline', basename(str_replace('\\', '/', $documento->nombre_original)));
        $response->setPrivate();

        return $response;
    }

    public function review(Request $request, SolicitudVendedor $solicitud)
    {
        $data = $request->validate(['estado' => ['required', 'in:aprobada,requiere_correccion,rechazada'], 'motivo_revision' => ['nullable', 'required_unless:estado,aprobada', 'string', 'min:10', 'max:2000']], ['motivo_revision.required_unless' => 'Explica al usuario qué debe corregir o el motivo del rechazo.', 'motivo_revision.min' => 'Escribe un motivo de al menos 10 caracteres.']);
        DB::transaction(function () use ($request, $solicitud, $data) {
            $user = $solicitud->usuario()->lockForUpdate()->firstOrFail();
            $application = SolicitudVendedor::lockForUpdate()->findOrFail($solicitud->getKey());
            if (! $application->estaEnRevision()) {
                throw ValidationException::withMessages(['estado' => 'Esta solicitud ya no está en revisión. Actualiza la página.']);
            }
            if ($data['estado'] === 'aprobada') {
                $documents = $application->documentos()->get()->keyBy('tipo_documento');
                foreach (['identificacion_frente', 'constancia_fiscal', 'comprobante_domicilio'] as $type) {
                    if (! isset($documents[$type]) || ! Storage::disk('local')->exists($documents[$type]->ruta_archivo)) {
                        throw ValidationException::withMessages(['estado' => 'No puedes aprobar la solicitud: faltan documentos obligatorios.']);
                    }
                }
                if (! $user->hasVerifiedEmail() || ! $user->activo) {
                    throw ValidationException::withMessages(['estado' => 'El usuario debe tener una cuenta activa y el correo verificado antes de habilitar las ventas.']);
                }
                $user->tipos_usuario()->syncWithoutDetaching([AccountRoles::id('comprador'), AccountRoles::id('vendedor')]);
            }
            $application->update(['estado' => $data['estado'], 'motivo_revision' => $data['motivo_revision'] ?? null, 'id_moderador' => $request->user()->id, 'fecha_revision' => now()]);
            $title = ['aprobada' => 'Tu cuenta de vendedor fue aprobada', 'requiere_correccion' => 'Necesitamos una corrección en tu solicitud de vendedor', 'rechazada' => 'Tu solicitud de vendedor fue rechazada'][$data['estado']];
            $user->notify(new BuyerActivityNotification($title, $data['motivo_revision'] ?? 'Ya puedes acceder al panel de vendedor y publicar tus productos.', route($data['estado'] === 'aprobada' ? 'vendedor.dashboard' : 'vendedor.register')));
        });

        $action = ['aprobada' => 'Aprobó una solicitud de vendedor', 'requiere_correccion' => 'Solicitó correcciones al vendedor', 'rechazada' => 'Rechazó una solicitud de vendedor'][$data['estado']];
        $request->attributes->set('staff_activity', ['accion' => $action, 'descripcion' => $action.' (solicitud #'.$solicitud->getKey().').', 'tipo_objeto' => 'solicitud_vendedor', 'id_objeto' => $solicitud->getKey()]);

        return back()->with('success', 'Revisión guardada. El usuario recibió una notificación.');
    }
}
