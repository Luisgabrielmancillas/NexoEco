<?php

use App\Models\User;
use App\Services\DemoMarketplacePopulator;
use App\Support\DemoCatalog;
use App\Support\ManzanilloBoundary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Http::preventStrayRequests();
    Mail::fake();
    Notification::fake();
    $this->freezeTime();
});

test('demo dry run writes nothing and does not grant roles to existing users', function () {
    $user = User::create(['name' => 'Cuenta existente', 'email' => 'original@example.test', 'password' => 'Original-test-123!', 'activo' => true]);
    $before = DB::table('users')->where('id', $user->id)->first();
    $this->artisan('demo:populate', ['--dry-run' => true])->assertSuccessful();
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseCount('tiendas', 0);
    $this->assertDatabaseCount('productos', 0);
    $this->assertDatabaseCount('tipos_usuario', 0);
    expect(DB::table('users')->where('id', $user->id)->first())->toEqual($before);
});

test('demo population creates coherent related data and is idempotent without financial records or external effects', function () {
    $original = User::create(['name' => 'Cuenta original', 'email' => 'original@example.test', 'password' => 'Original-test-123!', 'activo' => true]);
    $originalRow = DB::table('users')->where('id', $original->id)->first();
    $options = ['--stores' => 10, '--products' => 5, '--buyers' => 7];
    $this->artisan('demo:populate', $options)->assertSuccessful();
    foreach (['users' => 18, 'tiendas' => 10, 'productos' => 50, 'producto_imagenes' => 50, 'categorias' => 24, 'opiniones' => 30, 'product_chats' => 10, 'ch_conversation_participants' => 20, 'ch_messages' => 30, 'notifications' => 20, 'productos_favoritos' => 30, 'tiendas_favoritas' => 10, 'store_follows' => 10, 'solicitudes_soporte' => 2, 'respuestas_soporte' => 2, 'solicitudes_vendedor' => 2, 'reportes_contenido' => 1, 'seller_quick_replies' => 20, 'seller_automations' => 10, 'seller_store_designs' => 10, 'seller_daily_metrics' => 280, 'apartados' => 0, 'seller_subscriptions' => 0, 'mercado_pago_accounts' => 0] as $table => $count) {
        $this->assertDatabaseCount($table, $count);
    }
    $seller = User::where('email', DemoCatalog::email('seller', 1))->firstOrFail();
    expect($seller->tieneTipo('vendedor'))->toBeTrue()->and($seller->tieneTipo('comprador'))->toBeTrue()
        ->and(Hash::check(DemoMarketplacePopulator::PASSWORD, $seller->password))->toBeTrue();
    foreach (DB::table('tiendas')->get() as $shop) expect(app(ManzanilloBoundary::class)->contains((float) $shop->latitud, (float) $shop->longitud))->toBeTrue();
    foreach (DB::table('productos')->get() as $product) {
        expect(is_file(public_path(ltrim($product->imagen_url, '/'))))->toBeTrue();
        $this->assertDatabaseHas('producto_imagenes', ['id_producto' => $product->id_producto, 'es_principal' => true]);
    }
    $counts = [];
    foreach (DB::connection()->getSchemaBuilder()->getTableListing() as $table) $counts[$table] = DB::table($table)->count();
    $this->artisan('demo:populate', $options)->assertSuccessful();
    foreach ($counts as $table => $count) $this->assertDatabaseCount($table, $count);
    $this->artisan('demo:populate', $options + ['--verify' => true])->assertSuccessful();
    expect(DB::table('users')->where('id', $original->id)->first())->toEqual($originalRow);
    $buyer = User::where('email', DemoCatalog::email('buyer', 1))->firstOrFail();
    $product = \App\Models\Producto::where('codigo_producto', DemoCatalog::sku(1, 1))->firstOrFail();
    $this->actingAs($buyer)->get(route('comprador.dashboard'))->assertOk()->assertSee('[DEMO]');
    $this->get(route('productos.show', $product))->assertOk()->assertSee('categoria-01.svg');
    $this->getJson(route('chat.messages', \App\Models\ProductChat::where('id_producto', $product->getKey())->firstOrFail()))->assertOk()->assertJsonCount(3, 'messages');
    $this->actingAs($seller)->get(route('vendedor.dashboard'))->assertOk()->assertSee('DEMO');
    Notification::assertNothingSent();
    Mail::assertNothingOutgoing();
});

test('demo population refuses production and invalid volume options', function () {
    $this->artisan('demo:populate', ['--stores' => 0])->assertFailed();
    $this->artisan('demo:populate', ['--products' => '1.5'])->assertFailed();
    app()->detectEnvironment(fn () => 'production');
    $this->artisan('demo:populate', ['--dry-run' => true])->assertFailed();
    $this->assertDatabaseCount('users', 0);
});

test('a reserved demo email collision cannot overwrite an existing account', function () {
    $user = User::create(['name' => 'Cuenta ajena al generador', 'email' => DemoCatalog::email('buyer', 1), 'password' => 'Original-test-123!', 'activo' => false]);
    $before = DB::table('users')->where('id', $user->id)->first();
    $this->artisan('demo:populate', ['--stores' => 1, '--products' => 1, '--buyers' => 1])->assertFailed();
    expect(DB::table('users')->where('id', $user->id)->first())->toEqual($before);
    $this->assertDatabaseCount('usuario_tipo', 0);
    $this->assertDatabaseCount('productos', 0);
});

test('the Laravel demo seeder creates the same dataset and can be repeated without duplicates', function () {
    $seed = function () {
        $seeder = app(\Database\Seeders\NexoEcoDemoSeeder::class);
        $seeder->setContainer(app());
        $seeder(['stores' => 2, 'products' => 3, 'buyers' => 2]);
    };
    $seed();
    $this->assertDatabaseCount('users', 4);
    $this->assertDatabaseCount('tiendas', 2);
    $this->assertDatabaseCount('productos', 6);
    $this->assertDatabaseCount('producto_imagenes', 6);
    $this->assertDatabaseCount('ch_messages', 6);
    $counts = [];
    foreach (DB::connection()->getSchemaBuilder()->getTableListing() as $table) {
        $counts[$table] = DB::table($table)->count();
    }
    $seed();
    foreach ($counts as $table => $count) {
        $this->assertDatabaseCount($table, $count);
    }
    Notification::assertNothingSent();
    Mail::assertNothingOutgoing();
});

test('db seed defaults to the complete requested demo volumes instead of the skeleton test user', function () {
    $this->mock(DemoMarketplacePopulator::class, function ($mock) {
        $mock->shouldReceive('preflight')->once()->andReturn('sqlite / :memory:');
        $mock->shouldReceive('populate')->once()->with(500, 100, 500, \Mockery::type('callable'))->andReturn([]);
        $mock->shouldReceive('verify')->once()->with(500, 100, 500)->andReturn(['counts' => ['Tiendas DEMO' => 500, 'Productos DEMO' => 50000], 'errors' => []]);
    });
    $this->artisan('db:seed')->assertSuccessful();
    $this->assertDatabaseCount('users', 0);
});

test('the Laravel demo seeder refuses production before inserting data', function () {
    app()->detectEnvironment(fn () => 'production');
    $seeder = app(\Database\Seeders\NexoEcoDemoSeeder::class);
    $seeder->setContainer(app());
    expect(fn () => $seeder())->toThrow(\RuntimeException::class, 'local y testing');
    $this->assertDatabaseCount('users', 0);
});
