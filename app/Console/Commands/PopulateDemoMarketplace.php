<?php

namespace App\Console\Commands;

use App\Services\DemoMarketplacePopulator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class PopulateDemoMarketplace extends Command
{
    protected $signature = 'demo:populate
        {--stores=500 : Número de tiendas ficticias}
        {--products=100 : Productos por tienda}
        {--buyers=500 : Compradores ficticios}
        {--dry-run : Mostrar el plan sin escribir datos ni imágenes}
        {--verify : Verificar el conjunto existente sin escribir datos}';

    protected $description = 'Poblar el marketplace local con datos DEMO, sin borrar registros ni contactar servicios externos';

    public function handle(DemoMarketplacePopulator $populator): int
    {
        try {
            foreach (['stores' => 2000, 'products' => 500, 'buyers' => 5000] as $name => $max) {
                $value = (string) $this->option($name);
                if (! ctype_digit($value) || (int) $value < 1 || (int) $value > $max) {
                    throw new \InvalidArgumentException("--$name debe estar entre 1 y $max.");
                }
            }
            $stores = (int) $this->option('stores');
            $products = (int) $this->option('products');
            $buyers = (int) $this->option('buyers');
            $target = $populator->preflight();
            $this->info('Destino local: '.$target);
            $this->table(['Dato', 'Objetivo DEMO'], [
                ['Tiendas / vendedores', $stores], ['Compradores', $buyers],
                ['Productos', $stores * $products], ['Imágenes de producto', $stores * $products],
                ['Categorías', count(\App\Support\DemoCatalog::CATEGORIES)],
            ]);
            if ($this->option('dry-run')) {
                $this->comment('Simulación: no se escribió ningún registro ni archivo. Se conservarán todos los datos existentes.');

                return self::SUCCESS;
            }
            if (! $this->option('verify')) {
                $lock = Cache::store('file')->lock('nexoeco-demo-populate', 3600);
                if (! $lock->get()) {
                    throw new \RuntimeException('Ya hay otra carga DEMO en curso.');
                }
                try {
                    $created = $populator->populate($stores, $products, $buyers, function ($done) use ($stores) {
                        if ($done === 1 || $done % 25 === 0 || $done === $stores) {
                            $this->line("Tiendas completadas: $done/$stores");
                        }
                    });
                    if ($created) {
                        $this->table(['Tabla', 'Filas nuevas en esta ejecución'], collect($created)->map(fn ($count, $table) => [$table, $count])->values()->all());
                    } else {
                        $this->comment('El conjunto ya existía: 0 filas nuevas.');
                    }
                } finally {
                    $lock->release();
                }
            }
            $verification = $populator->verify($stores, $products, $buyers);
            $this->table(['Verificación', 'Resultado'], collect($verification['counts'])->map(fn ($count, $label) => [$label, $count])->values()->all());
            foreach ($verification['errors'] as $error) $this->error($error);
            if ($verification['errors']) return self::FAILURE;
            $this->info('Conjunto DEMO verificado. No se generaron pagos, correos, documentos oficiales ni credenciales financieras.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
