<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('subscriptions:sync', function () {
    $payments = app(\App\Services\MercadoPagoSubscriptions::class);
    if (! $payments->available()) {
        $this->warn('Suscripciones sin credenciales configuradas.');

        return;
    }
    $failed = 0;
    \App\Models\SellerSubscription::whereNotNull('provider_id')
        ->where(fn ($q) => $q->where('status', '!=', 'cancelled')->orWhere('paid_until', '>', now()))
        ->each(function ($subscription) use ($payments, &$failed) {
            try {
                \Illuminate\Support\Facades\DB::transaction(fn () => $payments->sync(\App\Models\SellerSubscription::whereKey($subscription->id)->lockForUpdate()->firstOrFail()));
            } catch (\Throwable $e) {
                $failed++;
                $this->error('No se pudo actualizar la suscripción '.$subscription->id.'.');
            }
        });
    if ($failed) {
        return 1;
    }
    $this->info('Suscripciones actualizadas.');
})->purpose('Reconciliar pagos y renovaciones de suscripciones de vendedores');

Schedule::command('subscriptions:sync')->everyFifteenMinutes()->withoutOverlapping();

Artisan::command('seller-growth:run', function () {
    app(\App\Services\SellerGrowthScheduler::class)->run();
    $this->info('Novedades, avisos de ofertas y recordatorios procesados.');
})->purpose('Publicar novedades, avisar de ofertas programadas y emitir recordatorios privados de apartados');

Schedule::command('seller-growth:run')->everyMinute()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
