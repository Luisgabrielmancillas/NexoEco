<?php

namespace Database\Seeders;

use App\Services\DemoMarketplacePopulator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class NexoEcoDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(DemoMarketplacePopulator $populator, int $stores = 500, int $products = 100, int $buyers = 500): void
    {
        validator(compact('stores', 'products', 'buyers'), [
            'stores' => ['integer', 'min:1', 'max:2000'],
            'products' => ['integer', 'min:1', 'max:500'],
            'buyers' => ['integer', 'min:1', 'max:5000'],
        ])->validate();

        $target = $populator->preflight();
        $lock = Cache::store('file')->lock('nexoeco-demo-populate', 3600);
        if (! $lock->get()) {
            throw new RuntimeException('Ya hay otra carga DEMO en curso.');
        }

        try {
            $this->command?->info('Cargando datos DEMO en '.$target);
            $created = $populator->populate($stores, $products, $buyers, function (int $done) use ($stores): void {
                if ($done === 1 || $done % 25 === 0 || $done === $stores) {
                    $this->command?->line("Tiendas completadas: $done/$stores");
                }
            });

            $verification = $populator->verify($stores, $products, $buyers);
            if ($verification['errors']) {
                throw new RuntimeException(implode(PHP_EOL, $verification['errors']));
            }

            $this->command?->table(['Dato DEMO', 'Cantidad verificada'], collect($verification['counts'])
                ->map(fn ($count, $label) => [$label, $count])->values()->all());
            $this->command?->info(number_format(array_sum($created)).' filas nuevas. Datos existentes conservados; sin pagos ni correos reales.');
        } finally {
            $lock->release();
        }
    }
}
