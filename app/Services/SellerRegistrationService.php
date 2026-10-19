<?php

namespace App\Services;

use App\Models\DocumentoVendedor;
use App\Models\SolicitudVendedor;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SellerRegistrationService
{
    public function register(array $data, UsuarioRolService $roles): User
    {
        return $this->save(function () use ($data, $roles) {
            $user = User::create([
                'name' => trim($data['nombre_completo']),
                'nombre_completo' => trim($data['nombre_completo']),
                'email' => $data['email'],
                'password' => $data['password'],
                'fecha_registro' => now(),
                'activo' => true,
            ]);
            $roles->asignarRolesRegistro($user, 'vendedor');

            return $user;
        }, $data);
    }

    public function submit(User $user, array $data): User
    {
        return $this->save(fn () => $user, $data);
    }

    private function save(Closure $resolveUser, array $data): User
    {
        $newPaths = [];
        $oldPaths = [];

        try {
            $user = DB::transaction(function () use ($resolveUser, $data, &$newPaths, &$oldPaths) {
                $user = $resolveUser();
                // Serialize submissions before creating or replacing documents.
                User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $solicitud = $user->solicitudVendedor()->firstOrCreate([], [
                    'estado' => SolicitudVendedor::ESTADO_PENDIENTE_DOCUMENTOS,
                    'fecha_solicitud' => now(),
                ]);

                if ($user->tieneTipo('vendedor') || ! in_array($solicitud->estado, [
                    SolicitudVendedor::ESTADO_PENDIENTE_DOCUMENTOS,
                    SolicitudVendedor::ESTADO_REQUIERE_CORRECCION,
                ], true)) {
                    throw ValidationException::withMessages([
                        'documentos' => 'Tu solicitud ya fue enviada y no puede modificarse en este momento.',
                    ]);
                }
                $correction = $solicitud->estado === SolicitudVendedor::ESTADO_REQUIERE_CORRECCION;

                $solicitud->update([
                    'rfc' => $data['rfc'],
                    'curp' => $data['curp'],
                    'telefono' => $data['telefono'],
                    'domicilio_fiscal' => $data['domicilio_fiscal'],
                    'tipo_persona' => 'fisica',
                ]);

                foreach (['identificacion_frente', 'identificacion_reverso', 'constancia_fiscal', 'comprobante_domicilio'] as $type) {
                    if (empty($data[$type])) {
                        continue;
                    }
                    $file = $data[$type];
                    $path = $file->storeAs('vendedores/'.$solicitud->id_solicitud, Str::uuid().'.'.$file->extension(), 'local');
                    if (! $path) {
                        throw new RuntimeException('No fue posible almacenar un documento.');
                    }
                    $newPaths[] = $path;
                    $previous = $solicitud->documentos()->where('tipo_documento', $type)->first();
                    if ($previous) {
                        $oldPaths[] = $previous->ruta_archivo;
                    }
                    DocumentoVendedor::updateOrCreate([
                        'id_solicitud' => $solicitud->id_solicitud,
                        'tipo_documento' => $type,
                    ], [
                        'ruta_archivo' => $path,
                        'nombre_original' => Str::limit(basename($file->getClientOriginalName()), 255, ''),
                        'mime_type' => $file->getMimeType(),
                        'tamano' => $file->getSize(),
                        'hash_sha256' => hash_file('sha256', $file->getRealPath()),
                        'estado_documento' => 'pendiente',
                        'fecha_subida' => now(),
                    ]);
                }

                $solicitud->update([
                    'estado' => SolicitudVendedor::ESTADO_EN_REVISION,
                    'fecha_envio' => now(),
                    'fecha_revision' => null,
                    'id_moderador' => null,
                    'motivo_revision' => null,
                ]);

                $user->notify(new \App\Notifications\BuyerActivityNotification(
                    'Tu cuenta de vendedor está en revisión',
                    'Recibimos tus datos y documentos. Puedes comprar mientras revisamos tu solicitud.',
                    route('vendedor.register')
                ));
                DB::afterCommit(function () use ($solicitud, $correction) {
                    try {
                        app(SellerReviewNotifier::class)->notifyReview($solicitud->id_solicitud, $correction);
                    } catch (Throwable $exception) {
                        // A mail failure must never undo saved documents or the account.
                        report($exception);
                    }
                });

                return $user;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($newPaths);
            throw $exception;
        }

        // Keep previous files intact until the database changes have committed.
        DB::afterCommit(fn () => Storage::disk('local')->delete($oldPaths));

        return $user;
    }
}
