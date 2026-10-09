<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class BuyerDatabase
{
    public static function migrate(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        // Use the real account/catalog migrations; unrelated legacy order indexes collide in SQLite.
        Artisan::call('migrate', ['--force' => true, '--path' => [
            'database/migrations/2026_05_08_165012_create_users_table.php',
            'database/migrations/2026_05_08_165012_create_tipos_usuario_table.php',
            'database/migrations/2026_05_08_165012_create_usuario_tipo_table.php',
            'database/migrations/2026_05_08_165012_create_tiendas_table.php',
            'database/migrations/2026_05_08_165012_create_categorias_table.php',
            'database/migrations/2026_05_08_165012_create_productos_table.php',
            'database/migrations/2026_05_08_165015_add_foreign_keys_to_usuario_tipo_table.php',
            'database/migrations/2026_05_28_172302_add_activo_to_users_table.php',
            'database/migrations/2026_09_18_213841_create_producto_imagenes_table.php',
            'database/migrations/2026_10_02_221100_create_solicitudes_vendedor_table.php',
            'database/migrations/2026_10_02_221102_create_documentos_vendedor_table.php',
            'database/migrations/2026_10_06_230000_create_buyer_account_tables.php',
            'database/migrations/2026_10_07_000000_add_profile_photos_and_support_replies.php',
            'database/migrations/2026_10_07_010000_create_registros_actividad_table.php',
            'database/migrations/2026_10_07_020000_add_store_business_details.php',
            'database/migrations/2026_10_07_030000_add_buyer_location_to_users.php',
            'database/migrations/2026_10_07_040000_add_content_moderation.php',
            'database/migrations/2026_10_07_050000_allow_direct_content_publication.php',
            'database/migrations/2026_07_18_000000_create_chatify_tables.php',
            'database/migrations/2026_10_09_000000_add_product_chat_and_reservations.php',
            'database/migrations/2026_10_09_010000_add_mercado_pago_accounts.php',
        ]]);
    }
}
