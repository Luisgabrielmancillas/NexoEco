<?php

use App\Models\Categoria;
use App\Models\Tienda;
use App\Models\TiposUsuario;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Http::preventStrayRequests();
    $role = TiposUsuario::create(['nombre_tipo' => 'vendedor']);
    $this->seller = User::create(['name' => 'Vendedor de prueba', 'email' => 'seller@carousel.test', 'password' => 'Testing-123!', 'activo' => true, 'email_verified_at' => now()]);
    $this->seller->tipos_usuario()->attach($role);
    $this->buyer = User::create(['name' => 'Comprador de prueba', 'email' => 'buyer@carousel.test', 'password' => 'Testing-123!', 'activo' => true, 'email_verified_at' => now()]);
    $this->buyer->forceFill(['ubicacion_comprador' => ['ciudad' => 'Manzanillo', 'estado' => 'Colima', 'latitud' => 19.0522, 'longitud' => -104.3158]])->save();
    $this->category = Categoria::create(['nombre_categoria' => 'Artesanías']);
    for ($index = 1; $index <= 31; $index++) {
        $shop = $this->seller->tiendas()->create(['nombre_tienda' => sprintf('Tienda %02d de nuestra comunidad', $index), 'descripcion_tienda' => 'Artesanías y productos locales para descubrir.', 'ciudad' => 'Manzanillo', 'colonia' => 'Zona de prueba', 'latitud' => 19.0522 + $index * .0001, 'longitud' => -104.3158, 'logo_tienda' => '/images/demo/tienda.svg', 'fecha_creacion' => now()->subMinutes($index)]);
        $shop->productos()->create(['id_categoria' => $this->category->getKey(), 'nombre_producto' => 'Producto '.$index, 'codigo_producto' => 'CAROUSEL-'.$index, 'precio' => 100]);
    }
    $this->seller->tiendas()->create(['nombre_tienda' => 'Tienda distante', 'latitud' => 19.243, 'longitud' => -103.724]);
});

test('home uses one row store carousels and real view all links instead of store pagination', function () {
    $response = $this->actingAs($this->buyer)->get(route('comprador.dashboard'))->assertOk()
        ->assertSee('data-store-carousel', false)->assertSee('nearby-store-track')->assertSee('local-store-track')
        ->assertSee(route('comprador.tiendas', ['cercanas' => 1]), false)->assertDontSee('pagina_tiendas=', false);
    expect($response->viewData('tiendasCercanas'))->toHaveCount(12)
        ->and($response->viewData('tiendas')->total())->toBe(32);
});

test('view all nearby stores preserves distance order filters and pagination without the preview limit', function () {
    $response = $this->actingAs($this->buyer)->get(route('comprador.tiendas', ['cercanas' => 1]))->assertOk()->assertSee('Todas las tiendas cerca de ti')->assertSee('Siguiente');
    $shops = $response->viewData('tiendas');
    expect($shops->total())->toBe(31)->and($shops->count())->toBe(24)
        ->and($shops->first()->nombre_tienda)->toBe('Tienda 01 de nuestra comunidad');
    expect($shops->pluck('distance_km')->all())->toBe($shops->pluck('distance_km')->sort()->values()->all());
    $this->get(route('comprador.tiendas', ['cercanas' => 1, 'page' => 2]))->assertOk()->assertViewHas('tiendas', fn ($items) => $items->count() === 7)->assertDontSee('Tienda distante');
    $this->get(route('comprador.tiendas', ['cercanas' => 1, 'q' => 'Tienda 01', 'categoria' => $this->category->getKey()]))->assertOk()->assertViewHas('tiendas', fn ($items) => $items->total() === 1);
});

test('store directory has useful empty states and requires a verified session', function () {
    $this->get(route('comprador.tiendas'))->assertRedirect(route('login'));
    $this->buyer->forceFill(['ubicacion_comprador' => null])->save();
    $this->actingAs($this->buyer)->get(route('comprador.tiendas', ['cercanas' => 1]))->assertOk()->assertSee('Elige tu ubicación para ver las tiendas cercanas');
    $this->get(route('comprador.tiendas', ['q' => 'No existe esta tienda']))->assertOk()->assertSee('No encontramos tiendas');
    $this->buyer->update(['email_verified_at' => null]);
    $this->get(route('comprador.tiendas'))->assertRedirect(route('verification.notice'));
});

test('carousel browser fixtures export only isolated test records', function () {
    if (getenv('NEXOECO_CAROUSEL_EXPORT') !== '1') {
        $this->actingAs($this->buyer)->get(route('comprador.tiendas'))->assertOk();
        return;
    }
    URL::forceRootUrl('http://127.0.0.1:4180');
    Vite::useHotFile(storage_path('framework/carousel-no-hot'));
    $directory = storage_path('framework/carousel-pages');
    File::ensureDirectoryExists($directory);
    $pages = ['home' => route('comprador.dashboard'), 'all' => route('comprador.tiendas'), 'nearby' => route('comprador.tiendas', ['cercanas' => 1])];
    foreach ($pages as $name => $url) {
        $response = $this->actingAs($this->buyer)->get($url)->assertOk();
        File::put($directory.'/'.$name.'.html', $response->getContent());
    }
});
