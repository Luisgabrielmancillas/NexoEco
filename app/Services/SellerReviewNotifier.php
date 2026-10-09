<?php

namespace App\Services;

use App\Jobs\RetrySellerReviewEmail;
use App\Models\SolicitudVendedor;
use App\Models\User;
use App\Notifications\SellerDocumentsReady;
use Throwable;

class SellerReviewNotifier
{
    public function revisionKey(SolicitudVendedor $application): string
    {
        return hash('sha256', $application->fecha_envio?->toISOString().'|'.$application->documentos()->orderBy('tipo_documento')->pluck('ruta_archivo')->implode('|'));
    }

    public function notifyReview(int $applicationId, bool $correction): void
    {
        $application = SolicitudVendedor::findOrFail($applicationId);
        $revision = $this->revisionKey($application);
        $moderators = User::where('activo', true)->whereNotNull('email_verified_at')
            ->whereHas('tipos_usuario', fn ($q) => $q->where('nombre_tipo', 'moderador'))->get();
        foreach ($moderators as $moderator) {
            try {
                $this->deliver($applicationId, $moderator->id, $revision, $correction);
            } catch (Throwable $exception) {
                report($exception);
                try {
                    // Keep retries durable even when the default queue is synchronous.
                    RetrySellerReviewEmail::dispatch($applicationId, $moderator->id, $revision, $correction)->onConnection('database')->delay(now()->addMinute());
                } catch (Throwable $queueException) {
                    report($queueException);
                }
            }
        }
    }

    public function deliver(int $applicationId, int $moderatorId, string $revision, bool $correction): void
    {
        $application = SolicitudVendedor::with('usuario')->find($applicationId);
        $moderator = User::find($moderatorId);
        if (! $application || ! $application->estaEnRevision() || $this->revisionKey($application) !== $revision
            || ! $moderator?->activo || ! $moderator->hasVerifiedEmail() || ! $moderator->tieneTipo('moderador')) {
            return;
        }
        $moderator->notify(new SellerDocumentsReady(
            $application->id_solicitud, $application->usuario->nombre_completo ?: $application->usuario->name,
            $correction, $application->fecha_envio->timezone('America/Mexico_City')->format('d/m/Y H:i'),
        ));
    }
}
