<?php

namespace App\Observers;

use App\Models\Apartado;
use App\Notifications\BuyerActivityNotification;

class ApartadoObserver
{
    public function created(Apartado $apartado): void
    {
        $apartado->seller?->notify(new BuyerActivityNotification(
            'Nueva solicitud de apartado',
            ($apartado->buyer?->name ?? 'Un comprador').' inició un apartado de '.$apartado->producto_nombre.'. El anticipo aún está pendiente de confirmación.',
            route('vendedor.apartados.index'),
            ['event' => 'apartado.created', 'apartado_id' => $apartado->id],
        ));
    }

    public function updated(Apartado $apartado): void
    {
        if (! $apartado->wasChanged('estado')) {
            return;
        }
        $status = match ($apartado->estado) {
            'pagado' => 'Anticipo confirmado', 'procesando' => 'Anticipo en proceso',
            'rechazado' => 'Anticipo rechazado', 'reembolso_parcial' => 'Reembolso parcial del anticipo',
            'reembolsado' => 'Anticipo reembolsado', 'revertido' => 'Anticipo revertido', default => null,
        };
        if (! $status) {
            return;
        }
        foreach (['buyer' => 'apartados.index', 'seller' => 'vendedor.apartados.index'] as $relation => $route) {
            $apartado->$relation?->notify(new BuyerActivityNotification(
                $status,
                $apartado->producto_nombre.' · $'.number_format((float) $apartado->monto, 2).' '.$apartado->moneda.'. Consulta el detalle de tu apartado.',
                route($route),
                ['event' => 'apartado.status', 'apartado_id' => $apartado->id, 'estado' => $apartado->estado],
            ));
        }
    }
}
