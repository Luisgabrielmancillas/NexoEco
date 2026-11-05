<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\BuyerActivityNotification;
use App\Support\DemoCatalog;
use App\Support\DesignTokens;
use App\Support\ManzanilloBoundary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class DemoMarketplacePopulator
{
    public const PASSWORD = 'NexoEco-Demo-2026!';

    private array $created = [];

    public function preflight(): string
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('La carga DEMO solo está habilitada en local y testing.');
        }
        $connection = DB::connection();
        if (! in_array($connection->getDriverName(), ['mysql', 'sqlite'], true)) {
            throw new RuntimeException('Solo se admiten MySQL local o SQLite para esta carga.');
        }
        if ($connection->getDriverName() === 'mysql') {
            $hosts = (array) $connection->getConfig('host');
            foreach ($hosts as $host) {
                if (! in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
                    throw new RuntimeException('La conexión no apunta a MySQL local. No se insertaron datos.');
                }
            }
            if ($connection->getConfig('read') || $connection->getConfig('write')) {
                throw new RuntimeException('No se admite una conexión con destinos separados de lectura/escritura.');
            }
        }
        foreach (['users', 'tipos_usuario', 'usuario_tipo', 'tiendas', 'categorias', 'productos', 'producto_imagenes', 'opiniones', 'productos_favoritos', 'tiendas_favoritas', 'store_follows', 'product_chats', 'ch_conversations', 'ch_conversation_participants', 'ch_messages', 'notifications', 'solicitudes_soporte', 'respuestas_soporte', 'solicitudes_vendedor', 'reportes_contenido', 'seller_quick_replies', 'seller_automations', 'seller_store_designs', 'seller_daily_metrics'] as $table) {
            if (! Schema::hasTable($table)) throw new RuntimeException("Falta la tabla $table. No se ejecutan migraciones automáticamente.");
        }
        foreach (['tiendas' => ['horarios', 'servicios', 'latitud', 'portada_tienda'], 'productos' => ['apartados_activos', 'deleted_at'], 'users' => ['activo', 'ubicacion_comprador']] as $table => $columns) {
            if (! Schema::hasColumns($table, $columns)) throw new RuntimeException("El esquema de $table está incompleto para la carga DEMO.");
        }

        return $connection->getDriverName().' / '.$connection->getDatabaseName();
    }

    private function insert(string $table, array $rows): void
    {
        if (! $rows) return;
        DB::table($table)->insert($rows);
        $this->created[$table] = ($this->created[$table] ?? 0) + count($rows);
    }

    private function first(string $table, array $key, array $values): object
    {
        if ($row = DB::table($table)->where($key)->first()) return $row;
        $this->insert($table, [$key + $values]);

        return DB::table($table)->where($key)->first();
    }

    private function user(string $kind, int $index, string $hash, array $roles): object
    {
        $email = DemoCatalog::email($kind, $index);
        $name = '[DEMO NexoEco] '.($kind === 'seller' ? 'Vendedor' : 'Comprador').' '.str_pad((string) $index, 4, '0', STR_PAD_LEFT);
        $user = $this->first('users', ['email' => $email], [
            'name' => $name, 'nombre_completo' => $name, 'password' => $hash, 'activo' => true,
            'ubicacion_comprador' => $kind === 'buyer' ? json_encode(['ciudad' => 'Manzanillo', 'estado' => 'Colima', 'latitud' => 19.0522, 'longitud' => -104.3158]) : null,
            'email_verified_at' => now(), 'fecha_registro' => now()->subDays(180), 'created_at' => now()->subDays(180), 'updated_at' => now(),
        ]);
        if (! str_starts_with($user->name, '[DEMO NexoEco]')) throw new RuntimeException("La dirección reservada $email ya pertenece a una cuenta ajena a esta carga.");
        foreach ($roles as $role) $this->first('usuario_tipo', ['id_usuario' => $user->id, 'id_tipo_usuario' => $role], []);

        return $user;
    }

    public function populate(int $stores, int $products, int $buyers, ?callable $progress = null): array
    {
        $this->preflight();
        $this->created = [];
        $this->images();
        $hash = Hash::make(self::PASSWORD);
        $roles = [];
        $categories = [];
        $buyerIds = [];
        DB::transaction(function () use (&$roles, &$categories, &$buyerIds, $buyers, $hash) {
            foreach (['comprador', 'vendedor'] as $role) {
                $row = DB::table('tipos_usuario')->whereRaw('LOWER(nombre_tipo) = ?', [$role])->first()
                    ?? $this->first('tipos_usuario', ['nombre_tipo' => $role], []);
                $roles[$role] = $row->id_tipo_usuario;
            }
            foreach (DemoCatalog::CATEGORIES as $index => [$name]) {
                $row = $this->first('categorias', ['nombre_categoria' => $name.' [DEMO]'], ['descripcion_categoria' => 'Categoría de demostración NexoEco para pruebas de catálogo.']);
                $categories[$index] = $row->id_categoria;
            }
            for ($index = 1; $index <= $buyers; $index++) $buyerIds[] = $this->user('buyer', $index, $hash, [$roles['comprador']])->id;
        });
        for ($index = 1; $index <= $stores; $index++) {
            DB::transaction(function () use ($index, $products, $hash, $roles, $categories, $buyerIds) {
                $seller = $this->user('seller', $index, $hash, [$roles['comprador'], $roles['vendedor']]);
                $category = ($index - 1) % count(DemoCatalog::CATEGORIES);
                $latitude = 19.0522 + (($index - 1) % 25 - 12) * .0003;
                $longitude = -104.3158 + (intdiv($index - 1, 25) % 20 - 10) * .0003;
                if (! app(ManzanilloBoundary::class)->contains($latitude, $longitude)) {
                    $latitude = 19.0522;
                    $longitude = -104.3158;
                }
                $hours = [];
                foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'] as $day) $hours[$day] = ['abierto' => $day !== 'domingo', 'inicio' => '09:00', 'fin' => '18:00'];
                $shop = $this->first('tiendas', ['id_vendedor' => $seller->id], [
                    'nombre_tienda' => DemoCatalog::CATEGORIES[$category][0].' · DEMO '.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                    'descripcion_tienda' => 'Tienda ficticia de NexoEco para explorar el catálogo, buscar productos y probar la plataforma. Los artículos, precios, ubicación y opiniones son de demostración.',
                    'logo_tienda' => '/images/demo/tienda.svg', 'portada_tienda' => '/images/demo/portada.svg',
                    'giro' => DemoCatalog::CATEGORIES[$category][0], 'tipo_negocio' => ['local', 'mixto', 'en_linea'][$index % 3],
                    'razon_social' => 'Negocio de demostración '.$index, 'email_contacto' => $seller->email,
                    'direccion' => 'Calle de demostración '.$index.' (dirección ficticia)', 'colonia' => 'Zona de demostración',
                    'ciudad' => 'Manzanillo', 'estado' => 'Colima', 'codigo_postal' => '28200',
                    'referencias' => 'DEMO NexoEco: no es la ubicación de un comercio real.',
                    'latitud' => $latitude, 'longitud' => $longitude,
                    'horarios' => json_encode($hours), 'servicios' => json_encode(['local', 'recoger']), 'fecha_creacion' => now()->subDays(120 + $index % 60),
                ]);
                if (! str_contains($shop->nombre_tienda, 'DEMO') || $shop->referencias !== 'DEMO NexoEco: no es la ubicación de un comercio real.') {
                    throw new RuntimeException("El vendedor DEMO $index tiene una tienda ajena al conjunto. Se detuvo la carga sin modificarla.");
                }
                $this->products($shop->id_tienda, $index, $products, $categories);
                $sample = DB::table('productos')->where('id_tienda', $shop->id_tienda)->where('codigo_producto', 'like', 'NE-DEMO-%')->orderBy('id_producto')->limit(3)->get();
                $buyer = $buyerIds[($index - 1) % count($buyerIds)];
                $this->related($index, $shop, $seller, $buyer, $sample);
            });
            if ($progress) $progress($index);
        }

        return $this->created;
    }

    private function products(int $shop, int $index, int $count, array $categories): void
    {
        $rows = [];
        $skus = [];
        $existing = DB::table('productos')->where('codigo_producto', 'like', sprintf('NE-DEMO-%04d-', $index).'%')->get()->keyBy('codigo_producto');
        foreach ($existing as $product) if ($product->id_tienda !== $shop) throw new RuntimeException('Un SKU DEMO ya está asociado a otra tienda.');
        for ($number = 1; $number <= $count; $number++) {
            $sku = DemoCatalog::sku($index, $number);
            $skus[] = $sku;
            if ($existing->has($sku)) continue;
            $category = intdiv(($index - 1) % 24, 3) * 3 + ($number - 1) % 3;
            [$label, $names] = DemoCatalog::CATEGORIES[$category];
            $name = $names[intdiv($number - 1, 3) % count($names)];
            $rows[] = [
                'id_tienda' => $shop, 'id_categoria' => $categories[$category], 'codigo_producto' => $sku,
                'nombre_producto' => $name.' · Variante '.str_pad((string) $number, 3, '0', STR_PAD_LEFT).' [DEMO]',
                'descripcion' => "$name de la categoría $label. Producto ficticio para pruebas de búsqueda, catálogo, favoritos y conversaciones. Variante $number de la tienda DEMO $index. No es una oferta comercial real.",
                'precio' => number_format((4000 + ($index * 173 + $number * 127) % 150000) / 100, 2, '.', ''),
                'imagen_url' => DemoCatalog::image($category), 'fecha_publicacion' => now()->subDays(($index + $number) % 90),
                'apartados_activos' => false,
            ];
        }
        foreach (array_chunk($rows, 250) as $chunk) $this->insert('productos', $chunk);
        $products = DB::table('productos')->where('id_tienda', $shop)->whereIn('codigo_producto', $skus)->get();
        $withImages = DB::table('producto_imagenes')->whereIn('id_producto', $products->pluck('id_producto'))->pluck('id_producto')->all();
        $images = [];
        foreach ($products as $product) {
            if (! in_array($product->id_producto, $withImages, true)) $images[] = ['id_producto' => $product->id_producto, 'imagen_url' => $product->imagen_url, 'es_principal' => true, 'orden' => 0, 'fecha_creacion' => now()];
        }
        foreach (array_chunk($images, 250) as $chunk) $this->insert('producto_imagenes', $chunk);
    }

    private function related(int $index, object $shop, object $seller, int $buyer, $products): void
    {
        $stamp = ['created_at' => now()->subDays($index % 30), 'updated_at' => now()];
        foreach ($products as $product) $this->first('productos_favoritos', ['id_usuario' => $buyer, 'id_producto' => $product->id_producto], $stamp);
        $this->first('tiendas_favoritas', ['id_usuario' => $buyer, 'id_tienda' => $shop->id_tienda], $stamp);
        $this->first('store_follows', ['user_id' => $buyer, 'id_tienda' => $shop->id_tienda], $stamp);
        foreach ($products->take(2) as $product) $this->first('opiniones', ['id_usuario' => $buyer, 'id_producto' => $product->id_producto], ['id_tienda' => null, 'calificacion' => 3 + $index % 3, 'comentario' => '[DEMO] Reseña ficticia de producto para probar calificaciones y comentarios.'] + $stamp);
        $this->first('opiniones', ['id_usuario' => $buyer, 'id_tienda' => $shop->id_tienda], ['id_producto' => null, 'calificacion' => 3 + $index % 3, 'comentario' => '[DEMO] Opinión ficticia de tienda. No corresponde a una compra real.'] + $stamp);
        $this->chat($index, $products->first()->id_producto, $seller->id, $buyer);
        if ($index % 5 === 0) {
            $ticket = $this->first('solicitudes_soporte', ['id_usuario' => $buyer, 'asunto' => '[DEMO] Consulta de la tienda '.$index], ['mensaje' => 'Consulta ficticia para comprobar el panel de soporte.', 'estado' => 'abierta'] + $stamp);
            $this->first('respuestas_soporte', ['id_solicitud' => $ticket->id, 'id_usuario' => $buyer], ['es_administrador' => false, 'mensaje' => '[DEMO] Información adicional de la consulta de prueba.'] + $stamp);
            $this->first('solicitudes_vendedor', ['id_usuario' => $buyer], ['estado' => 'pendiente_documentos', 'motivo_revision' => '[DEMO] Solicitud ficticia, sin documentos oficiales.', 'fecha_solicitud' => now()]);
        }
        if ($index % 10 === 0) $this->first('reportes_contenido', ['id_usuario' => $buyer, 'tipo' => 'productos', 'id_contenido' => $products->first()->id_producto], ['categoria' => 'otro', 'motivo' => '[DEMO] Reporte ficticio para comprobar el panel de moderación.', 'estado' => 'abierto'] + $stamp);
        foreach (['Disponibilidad' => 'Hola, gracias por escribir. Esta es una respuesta de demostración.', 'Entrega' => 'Podemos acordar los detalles de entrega por este chat de prueba.'] as $title => $body) $this->first('seller_quick_replies', ['user_id' => $seller->id, 'title' => '[DEMO] '.$title], ['body' => $body] + $stamp);
        $this->first('seller_automations', ['user_id' => $seller->id, 'kind' => 'welcome'], ['body' => '[DEMO] Bienvenido a esta tienda de prueba.', 'enabled' => false] + $stamp);
        $this->first('seller_store_designs', ['user_id' => $seller->id], ['theme' => 'oceano', 'tagline' => 'Tienda de demostración NexoEco', 'story' => 'Historia ficticia para probar la personalización.', 'layout' => 'clasico'] + $stamp);
        for ($day = 1; $day <= 7; $day++) {
            foreach (['store' => [$shop->id_tienda], 'product' => $products->pluck('id_producto')->all()] as $kind => $ids) {
                foreach ($ids as $id) $this->first('seller_daily_metrics', ['seller_id' => $seller->id, 'kind' => $kind, 'target_id' => $id, 'day' => now()->subDays($day)->toDateString()], ['visits' => 1 + ($index + $day) % 30]);
            }
        }
    }

    private function chat(int $index, int $product, int $seller, int $buyer): void
    {
        $stamp = ['created_at' => now()->subDays($index % 20)->subMinutes(10), 'updated_at' => now()];
        $existingChat = DB::table('product_chats')->where(['id_producto' => $product, 'buyer_id' => $buyer])->first();
        if ($existingChat && (int) $existingChat->seller_id !== $seller) throw new RuntimeException('La conversación existente pertenece a otro vendedor.');
        $conversationId = $existingChat?->conversation_id ?? Uuid::uuid5(Uuid::NAMESPACE_URL, "nexoeco-demo-conversation-$product-$buyer")->toString();
        $this->first('ch_conversations', ['id' => $conversationId], ['type' => 'direct', 'name' => '[DEMO] Consulta del producto '.$index, 'created_by' => $buyer] + $stamp);
        foreach ([$buyer, $seller] as $user) $this->first('ch_conversation_participants', ['conversation_id' => $conversationId, 'user_id' => $user], ['id' => Uuid::uuid5(Uuid::NAMESPACE_URL, "nexoeco-demo-participant-$conversationId-$user")->toString(), 'role' => 'member'] + $stamp);
        $chat = $this->first('product_chats', ['id_producto' => $product, 'buyer_id' => $buyer], ['seller_id' => $seller, 'conversation_id' => $conversationId, 'negotiation_status' => ['nuevo', 'negociacion', 'entrega'][$index % 3], 'negotiation_notes' => '[DEMO] Conversación ficticia sin venta ni pago.'] + $stamp);
        foreach (['[DEMO] Hola, ¿puedes contarme más de este producto de prueba?', '[DEMO] Claro, podemos revisar sus detalles en esta conversación.', '[DEMO] Gracias. Este mensaje permite probar el historial del chat.'] as $offset => $body) {
            $this->first('ch_messages', ['id' => Uuid::uuid5(Uuid::NAMESPACE_URL, "nexoeco-demo-message-$conversationId-$offset")->toString()], ['conversation_id' => $conversationId, 'user_id' => $offset === 1 ? $seller : $buyer, 'kind' => 'user', 'body' => $body, 'created_at' => now()->subDays($index % 20)->subMinutes(9 - $offset), 'updated_at' => now()]);
        }
        foreach ([$buyer, $seller] as $user) $this->first('notifications', ['id' => Uuid::uuid5(Uuid::NAMESPACE_URL, "nexoeco-demo-notification-$conversationId-$user")->toString()], [
            'type' => BuyerActivityNotification::class, 'notifiable_type' => User::class, 'notifiable_id' => $user,
            'data' => json_encode(['titulo' => '[DEMO] Conversación de prueba', 'mensaje' => 'Aviso ficticio para explorar las notificaciones.', 'url' => '/mensajes/'.$chat->id, 'event' => 'demo.chat'], JSON_UNESCAPED_UNICODE),
            'read_at' => $index % 3 === 0 ? now() : null,
        ] + $stamp);
    }

    public function verify(int $stores, int $products, int $buyers): array
    {
        $sellers = DB::table('users')->whereIn('email', array_map(fn ($i) => DemoCatalog::email('seller', $i), range(1, $stores)))->pluck('id');
        $shops = DB::table('tiendas')->whereIn('id_vendedor', $sellers)->pluck('id_tienda');
        $items = DB::table('productos')->whereIn('id_tienda', $shops)->where('codigo_producto', 'like', 'NE-DEMO-%');
        $counts = [
            'Vendedores DEMO' => $sellers->count(), 'Compradores DEMO' => DB::table('users')->whereIn('email', array_map(fn ($i) => DemoCatalog::email('buyer', $i), range(1, $buyers)))->count(),
            'Tiendas DEMO' => $shops->count(), 'Productos DEMO' => (clone $items)->count(),
            'Imágenes principales DEMO' => DB::table('producto_imagenes')->whereIn('id_producto', (clone $items)->select('id_producto'))->where('es_principal', true)->count(),
            'Categorías DEMO' => DB::table('categorias')->whereIn('nombre_categoria', array_map(fn ($category) => $category[0].' [DEMO]', DemoCatalog::CATEGORIES))->count(),
            'Conversaciones DEMO' => DB::table('product_chats')->whereIn('id_producto', (clone $items)->select('id_producto'))->count(),
            'Reseñas DEMO' => DB::table('opiniones')->where('comentario', 'like', '[DEMO]%')->where(fn ($query) => $query->whereIn('id_producto', (clone $items)->select('id_producto'))->orWhereIn('id_tienda', $shops))->count(),
        ];
        $errors = [];
        foreach (['Vendedores DEMO' => $stores, 'Compradores DEMO' => $buyers, 'Tiendas DEMO' => $stores, 'Productos DEMO' => $stores * $products, 'Imágenes principales DEMO' => $stores * $products, 'Categorías DEMO' => count(DemoCatalog::CATEGORIES)] as $label => $expected) {
            if ($counts[$label] !== $expected) $errors[] = "$label: se esperaban $expected y se encontraron {$counts[$label]}.";
        }
        $wrongStores = (clone $items)->select('id_tienda')->groupBy('id_tienda')->havingRaw('COUNT(*) != ?', [$products])->get()->count();
        if ($wrongStores) $errors[] = "$wrongStores tiendas no tienen la cantidad solicitada de productos.";
        $withoutImage = (clone $items)->whereNotExists(fn ($query) => $query->selectRaw('1')->from('producto_imagenes')->whereColumn('producto_imagenes.id_producto', 'productos.id_producto')->where('es_principal', true))->count();
        if ($withoutImage) $errors[] = "$withoutImage productos no tienen una imagen principal.";
        if ((clone $items)->where('apartados_activos', true)->exists()) $errors[] = 'Hay apartados activados en el conjunto DEMO; revisa los cambios manuales.';

        return compact('counts', 'errors');
    }

    private function images(): void
    {
        $directory = public_path('images/demo');
        File::ensureDirectoryExists($directory);
        $primary = DesignTokens::color('primary');
        $soft = DesignTokens::color('primary-soft');
        $ink = DesignTokens::color('text-primary');
        foreach (DemoCatalog::CATEGORIES as $index => [$label]) {
            $label = htmlspecialchars($label, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="0 0 800 800"><rect width="800" height="800" rx="48" fill="'.$soft.'"/><circle cx="400" cy="320" r="164" fill="white"/><g fill="none" stroke="'.$primary.'" stroke-width="14" stroke-linejoin="round"><path d="m300 275 100-55 100 55v125l-100 55-100-55zM300 275l100 55 100-55M400 330v125M350 248l100 55"/></g><text x="400" y="570" text-anchor="middle" font-family="sans-serif" font-size="34" font-weight="700" fill="'.$ink.'">'.$label.'</text><text x="400" y="625" text-anchor="middle" font-family="sans-serif" font-size="24" fill="'.$primary.'">NexoEco · Imagen de demostración</text></svg>';
            $file = public_path(ltrim(DemoCatalog::image($index), '/'));
            if (! File::exists($file)) File::put($file, $svg);
        }
        if (! File::exists($directory.'/tienda.svg')) File::put($directory.'/tienda.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400"><rect width="400" height="400" rx="60" fill="'.$soft.'"/><g fill="none" stroke="'.$primary.'" stroke-width="12" stroke-linejoin="round"><path d="M90 165h220l-25-65H115zM110 165v130h180V165M175 295v-80h50v80"/></g><text x="200" y="350" text-anchor="middle" font-size="24" font-family="sans-serif" fill="'.$primary.'">DEMO NexoEco</text></svg>');
        if (! File::exists($directory.'/portada.svg')) File::put($directory.'/portada.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="600" viewBox="0 0 1600 600"><rect width="1600" height="600" fill="'.$primary.'"/><circle cx="1350" cy="80" r="280" fill="white" opacity=".08"/><circle cx="80" cy="600" r="220" fill="white" opacity=".08"/><text x="800" y="280" text-anchor="middle" font-family="sans-serif" font-size="84" font-weight="700" fill="white">NexoEco</text><text x="800" y="360" text-anchor="middle" font-family="sans-serif" font-size="34" fill="white">Comercio local · Tienda de demostración</text></svg>');
    }
}
