<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitudVendedorRequest;
use App\Models\DocumentoVendedor;
use App\Models\SolicitudVendedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class SolicitudVendedorController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();


        if ($user->tieneTipo('vendedor')) {
            return redirect()
                ->route('vendedor.dashboard');
        }


        $solicitud = $user
            ->solicitudVendedor()
            ->with('documentos')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | COMPRADOR QUE DECIDIÓ VENDER DESPUÉS
        |--------------------------------------------------------------------------
        */

        if (!$solicitud) {
            $solicitud = SolicitudVendedor::create([
                'id_usuario' => $user->id,

                'estado' =>
                    SolicitudVendedor::ESTADO_PENDIENTE_DOCUMENTOS,

                'fecha_solicitud' => now(),
            ]);
        }


        return view(
            'marketplace.solicitud-vendedor',
            compact('solicitud')
        );
    }


    public function store(
        StoreSolicitudVendedorRequest $request
    ): RedirectResponse {
        $user = $request->user();


        $solicitud = $user
            ->solicitudVendedor()
            ->firstOrCreate(
                [],
                [
                    'estado' =>
                        SolicitudVendedor::ESTADO_PENDIENTE_DOCUMENTOS,

                    'fecha_solicitud' => now(),
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | NO PERMITIR MODIFICACIÓN DURANTE REVISIÓN
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $solicitud->estado,
                [
                    SolicitudVendedor::ESTADO_PENDIENTE_DOCUMENTOS,
                    SolicitudVendedor::ESTADO_REQUIERE_CORRECCION,
                ],
                true
            )
        ) {
            return redirect()
                ->route(
                    'vendedor.solicitud.create'
                )
                ->with(
                    'status',
                    'Tu solicitud ya fue enviada y no puede modificarse en este momento.'
                );
        }


        $archivosNuevos = [];


        try {

            DB::transaction(
                function () use (
                    $request,
                    $solicitud,
                    &$archivosNuevos
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | DATOS
                    |--------------------------------------------------------------------------
                    */

                    $solicitud->update([
                        'rfc' =>
                            $request->validated('rfc'),

                        'curp' =>
                            $request->validated('curp'),

                        'telefono' =>
                            $request->validated('telefono'),

                        'domicilio_fiscal' =>
                            $request->validated(
                                'domicilio_fiscal'
                            ),

                        'tipo_persona' =>
                            'fisica',
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | DOCUMENTOS
                    |--------------------------------------------------------------------------
                    */

                    $tipos = [
                        'identificacion_frente',
                        'identificacion_reverso',
                        'constancia_fiscal',
                        'comprobante_domicilio',
                    ];


                    foreach ($tipos as $tipo) {

                        if (!$request->hasFile($tipo)) {
                            continue;
                        }


                        $archivo =
                            $request->file($tipo);


                        $extension = mb_strtolower(
                            $archivo->extension()
                        );


                        $nombreSeguro =
                            Str::uuid()->toString()
                            . '.'
                            . $extension;


                        /*
                        |--------------------------------------------------------------------------
                        | STORAGE PRIVADO
                        |--------------------------------------------------------------------------
                        |
                        | disk local NO genera URL pública.
                        |
                        */

                        $ruta = $archivo->storeAs(
                            'vendedores/'
                                . $solicitud->id_solicitud,
                            $nombreSeguro,
                            'local'
                        );


                        if (!$ruta) {
                            throw new \RuntimeException(
                                'No fue posible almacenar un documento.'
                            );
                        }


                        $archivosNuevos[] = $ruta;


                        $documentoAnterior =
                            DocumentoVendedor::query()
                                ->where(
                                    'id_solicitud',
                                    $solicitud->id_solicitud
                                )
                                ->where(
                                    'tipo_documento',
                                    $tipo
                                )
                                ->first();


                        $hash = hash_file(
                            'sha256',
                            $archivo->getRealPath()
                        );


                        DocumentoVendedor::query()
                            ->updateOrCreate(
                                [
                                    'id_solicitud' =>
                                        $solicitud->id_solicitud,

                                    'tipo_documento' =>
                                        $tipo,
                                ],
                                [
                                    'ruta_archivo' =>
                                        $ruta,

                                    'nombre_original' =>
                                        Str::limit(
                                            basename(
                                                $archivo
                                                    ->getClientOriginalName()
                                            ),
                                            255,
                                            ''
                                        ),

                                    'mime_type' =>
                                        $archivo->getMimeType()
                                        ?: 'application/octet-stream',

                                    'tamano' =>
                                        $archivo->getSize(),

                                    'hash_sha256' =>
                                        $hash,

                                    'estado_documento' =>
                                        'pendiente',

                                    'fecha_subida' =>
                                        now(),
                                ]
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | BORRAR ARCHIVO REEMPLAZADO
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $documentoAnterior
                            && $documentoAnterior
                                ->ruta_archivo
                                !== $ruta
                        ) {
                            Storage::disk('local')
                                ->delete(
                                    $documentoAnterior
                                        ->ruta_archivo
                                );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ENVIAR A REVISIÓN
                    |--------------------------------------------------------------------------
                    */

                    $solicitud->update([
                        'estado' =>
                            SolicitudVendedor::ESTADO_EN_REVISION,

                        'fecha_envio' =>
                            now(),

                        'fecha_revision' =>
                            null,

                        'id_moderador' =>
                            null,

                        'motivo_revision' =>
                            null,
                    ]);
                }
            );

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | LIMPIAR ARCHIVOS SI FALLA BD
            |--------------------------------------------------------------------------
            */

            foreach ($archivosNuevos as $ruta) {
                Storage::disk('local')
                    ->delete($ruta);
            }


            report($exception);


            return back()
                ->withInput()
                ->withErrors([
                    'documentos' =>
                        'No fue posible enviar tu solicitud. Intenta nuevamente.',
                ]);
        }


        return redirect()
            ->route(
                'vendedor.solicitud.create'
            )
            ->with(
                'status',
                'Tu solicitud fue enviada correctamente y ahora está en revisión.'
            );
    }
}