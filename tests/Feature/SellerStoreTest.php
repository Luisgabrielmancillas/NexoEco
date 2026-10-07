<?php

use App\Http\Requests\StoreBusinessRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\TiposUsuario;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Storage::fake('local');
    foreach (['comprador', 'vendedor', 'administrador'] as $name) {
        TiposUsuario::create(['nombre_tipo' => $name]);
    }
    $this->seller = User::create(['name' => 'Vendedor autorizado', 'email' => 'tienda@example.test', 'password' => 'clave-de-prueba', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer = User::create(['name' => 'Comprador', 'email' => 'cliente@example.test', 'password' => 'clave-de-prueba', 'email_verified_at' => now(), 'activo' => true]);
    $this->seller->tipos_usuario()->attach(TiposUsuario::whereIn('nombre_tipo', ['comprador', 'vendedor'])->pluck('id_tipo_usuario'));
    $this->buyer->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'comprador')->value('id_tipo_usuario'));
});

function shopBusinessData(): array
{
    $hours = [];
    foreach (StoreBusinessRequest::DAYS as $day => $label) {
        $hours[$day] = ['abierto' => $day === 'lunes' ? '1' : '0', 'inicio' => '22:00', 'fin' => '02:00'];
    }

    return ['nombre_tienda' => 'Cerámica del puerto', 'descripcion_tienda' => 'Cerámica hecha a mano por artesanos de Manzanillo.', 'giro' => 'Artesanías', 'tipo_negocio' => 'local', 'telefono' => '314 123 4567', 'email_contacto' => 'contacto@example.test', 'direccion' => 'Avenida México 10', 'colonia' => 'Centro', 'ciudad' => 'Manzanillo', 'estado' => 'Colima', 'codigo_postal' => '28200', 'latitud' => '19.0522000', 'longitud' => '-104.3158000', 'horarios' => $hours, 'servicios' => ['recoger', 'local'], 'logo' => UploadedFile::fake()->image('logo.png', 128, 128), 'portada' => UploadedFile::fake()->image('portada.jpg', 800, 300)];
}

function createShopFor(User $owner): Tienda
{
    return $owner->tiendas()->create(['nombre_tienda' => 'Tienda de '.$owner->name, 'fecha_creacion' => now(), 'descripcion_tienda' => 'Productos locales de la comunidad.']);
}

test('only approved sellers see the shop link beside logout and get the initial setup gate', function () {
    $this->actingAs($this->buyer)->get(route('comprador.dashboard'))->assertOk()->assertDontSee('class="buyer-nav-link buyer-store-link"', false);
    $this->actingAs($this->seller)->get(route('comprador.dashboard'))->assertOk()->assertSeeInOrder(['Mi tienda</a>', 'Cerrar sesión</button>'], false);
    $this->get(route('profile.edit'))->assertOk()->assertDontSee('Panel de vendedor');
    foreach (['vendedor.dashboard', 'vendedor.productos.index', 'vendedor.productos.create'] as $route) {
        $this->get(route($route))->assertRedirect(route('vendedor.tienda.create'));
    }
    $this->get(route('vendedor.tienda.create'))->assertOk()->assertSee('Crea tu tienda en NexoEco')->assertSee('data-shop-map', false)->assertSee('Navegación de mi tienda')->assertDontSee('Navegación del comprador');
});

test('seller routes reject anonymous pending unverified inactive and administrator accounts', function () {
    $routes = ['vendedor.dashboard', 'vendedor.tienda.create', 'vendedor.tienda.edit', 'vendedor.productos.index', 'vendedor.productos.create'];
    foreach ($routes as $route) {
        $this->get(route($route))->assertRedirect(route('login'));
        $this->actingAs($this->buyer)->get(route($route))->assertForbidden();
        auth()->logout();
    }
    $this->actingAs($this->buyer)->post(route('vendedor.tienda.store'), shopBusinessData())->assertForbidden();
    $this->seller->update(['email_verified_at' => null]);
    $this->actingAs($this->seller)->get(route('vendedor.dashboard'))->assertRedirect(route('verification.notice'));
    $this->post(route('vendedor.tienda.store'), shopBusinessData())->assertRedirect(route('verification.notice'));
    $this->seller->update(['email_verified_at' => now(), 'activo' => false]);
    $this->get(route('vendedor.dashboard'))->assertRedirect(route('login'));
    $this->seller->update(['activo' => true]);
    $this->seller->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'administrador')->value('id_tipo_usuario'));
    $this->actingAs($this->seller)->get(route('vendedor.dashboard'))->assertRedirect(route('administrador.dashboard'));
    $this->post(route('vendedor.tienda.store'), shopBusinessData())->assertForbidden();
    $this->assertDatabaseCount('tiendas', 0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('shop creation persists ownership normalized hours and brand images then opens the summary', function () {
    $data = shopBusinessData() + ['id_vendedor' => $this->buyer->id, 'fecha_creacion' => '2000-01-01'];
    $this->actingAs($this->seller)->post(route('vendedor.tienda.store'), $data)->assertRedirect(route('vendedor.dashboard'))->assertSessionHasNoErrors();
    $shop = $this->seller->tiendas()->firstOrFail();
    expect($shop->id_vendedor)->toBe($this->seller->id)->and($shop->fecha_creacion->isToday())->toBeTrue()
        ->and($shop->horarios['lunes'])->toBe(['abierto' => true, 'inicio' => '22:00', 'fin' => '02:00'])
        ->and($shop->horarios['martes'])->toBe(['abierto' => false, 'inicio' => null, 'fin' => null]);
    $this->get(route('vendedor.dashboard'))->assertOk()->assertSee('Cerámica del puerto')->assertSee('Sin calificaciones')->assertSee('Datos de tu actividad real')->assertSee('22:00 – 02:00');
    $this->get(route('vendedor.tienda.create'))->assertRedirect(route('vendedor.dashboard'));
    $this->get(route('tiendas.show', $shop))->assertOk()->assertSee('Avenida México 10')->assertSee('Horario de atención')->assertSee('export/embed.html', false)->assertSee($shop->portada_tienda, false);
    auth()->logout();
    $this->get($shop->logo_tienda)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('Content-Type', 'image/png');
    $this->get(route('marketplace.index'))->assertOk()->assertSee('Cerámica del puerto');
    $this->get(route('tiendas.show', $shop))->assertRedirect(route('login'));
});

test('duplicate shop submissions cannot create a second store or overwrite the existing store', function () {
    $shop = createShopFor($this->seller);
    $this->actingAs($this->seller)->post(route('vendedor.tienda.store'), shopBusinessData())->assertRedirect(route('vendedor.dashboard'));
    $this->assertDatabaseCount('tiendas', 1);
    expect($shop->fresh()->nombre_tienda)->toBe('Tienda de Vendedor autorizado')->and(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('stores outside Manzanillo cannot be created even with a forged city name', function () {
    $this->actingAs($this->seller);
    foreach ([[19.243, -103.724], [18.938, -103.964], [19.386, -104.045], [19.0522, -104.6]] as [$latitude, $longitude]) {
        $data = shopBusinessData();
        $data['latitud'] = $latitude;
        $data['longitud'] = $longitude;
        $this->postJson(route('vendedor.tienda.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('latitud')->assertJsonFragment(['latitud' => [\App\Support\ManzanilloBoundary::MESSAGE]]);
    }
    $this->assertDatabaseCount('tiendas', 0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('store edits cannot move a Manzanillo business outside the service area', function () {
    $this->actingAs($this->seller)->post(route('vendedor.tienda.store'), shopBusinessData())->assertSessionHasNoErrors();
    $shop = $this->seller->tiendas()->first();
    $data = shopBusinessData();
    unset($data['logo'], $data['portada']);
    $data['latitud'] = '19.243';
    $data['longitud'] = '-103.724';
    $this->put(route('vendedor.tienda.update'), $data)->assertSessionHasErrors('latitud');
    expect((float) $shop->fresh()->latitud)->toBe(19.0522);
});

test('shop validation rejects missing branding invalid locations unsafe URLs and invalid opening days', function () {
    $data = shopBusinessData();
    unset($data['logo'], $data['portada']);
    $data['latitud'] = '91';
    $data['longitud'] = '-181';
    $data['sitio_web'] = 'javascript:alert(1)';
    $data['codigo_postal'] = 'ABC';
    $data['horarios']['lunes']['inicio'] = 'mal';
    $data['horarios']['lunes']['fin'] = '';
    $this->actingAs($this->seller)->post(route('vendedor.tienda.store'), $data)->assertSessionHasErrors(['logo', 'portada', 'latitud', 'longitud', 'sitio_web', 'codigo_postal', 'horarios.lunes.inicio', 'horarios.lunes.fin']);
    $data = shopBusinessData();
    foreach ($data['horarios'] as &$hours) {
        $hours['abierto'] = '0';
    }
    unset($hours);
    $this->post(route('vendedor.tienda.store'), $data)->assertSessionHasErrors('horarios');
    $this->assertDatabaseCount('tiendas', 0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('shop editing preserves branding unless replaced and never edits another owner', function () {
    $this->actingAs($this->seller)->post(route('vendedor.tienda.store'), shopBusinessData())->assertSessionHasNoErrors();
    $own = $this->seller->tiendas()->first();
    $other = createShopFor($this->buyer);
    $data = shopBusinessData();
    unset($data['logo'], $data['portada']);
    $data['nombre_tienda'] = 'Nueva identidad';
    $data['id_tienda'] = $other->getKey();
    $data['id_vendedor'] = $this->buyer->id;
    $this->put(route('vendedor.tienda.update'), $data)->assertSessionHasNoErrors()->assertRedirect(route('vendedor.dashboard'));
    expect($own->fresh()->nombre_tienda)->toBe('Nueva identidad')->and($own->fresh()->logo_tienda)->toBe($own->logo_tienda)->and($other->fresh()->nombre_tienda)->toBe('Tienda de Comprador');
    $data['logo'] = UploadedFile::fake()->image('new-logo.webp', 128, 128);
    $this->put(route('vendedor.tienda.update'), $data)->assertSessionHasNoErrors();
    $this->get($own->logo_tienda)->assertNotFound();
    $this->get($own->fresh()->logo_tienda)->assertOk();
    Storage::disk('local')->assertMissing(ltrim(str_replace('/media/', '', $own->logo_tienda), '/'));
});

test('failed shop persistence removes new uploads and preserves existing images', function () {
    $this->actingAs($this->seller)->post(route('vendedor.tienda.store'), shopBusinessData())->assertSessionHasNoErrors();
    $before = Storage::disk('local')->allFiles();
    $shop = $this->seller->tiendas()->first();
    Tienda::updating(function () {
        throw new RuntimeException('Simulated store failure');
    });
    try {
        $this->withoutExceptionHandling();
        try {
            $this->put(route('vendedor.tienda.update'), shopBusinessData());
            $this->fail('Expected failure');
        } catch (RuntimeException $e) {
            expect($e->getMessage())->toBe('Simulated store failure');
        }
        expect(Storage::disk('local')->allFiles())->toBe($before)->and($shop->fresh()->logo_tienda)->toBe($shop->logo_tienda);
    } finally {
        Tienda::flushEventListeners();
    }
});

test('product publication is scoped to the seller and appears in the real public catalog', function () {
    $shop = createShopFor($this->seller);
    $other = createShopFor($this->buyer);
    $this->actingAs($this->seller)->get(route('vendedor.productos.create'))->assertOk()->assertDontSee('Navegación del comprador');
    $this->post(route('vendedor.productos.store'), ['nombre_producto' => 'Taza del puerto', 'descripcion' => 'Una taza artesanal.', 'precio' => '175.50', 'nueva_categoria' => 'Cerámica', 'imagen' => UploadedFile::fake()->image('taza.jpg', 300, 300), 'id_tienda' => $other->getKey(), 'codigo_producto' => 'INJECTED'])->assertRedirect(route('vendedor.productos.index'))->assertSessionHasNoErrors();
    $product = Producto::firstOrFail();
    expect($product->id_tienda)->toBe($shop->getKey())->and($product->precio)->toBe(175.50)->and($product->codigo_producto)->toStartWith('NE-');
    $this->get(route('vendedor.productos.index'))->assertOk()->assertSee('Taza del puerto');
    expect($product->estado_moderacion)->toBe('aprobada');
    $this->get(route('productos.show', $product))->assertOk();
    $product->forceFill(['estado_moderacion' => 'aprobada'])->save();
    $this->get(route('productos.show', $product))->assertOk()->assertSee('Taza del puerto');
    auth()->logout();
    $this->get(route('marketplace.index'))->assertOk()->assertSee('Taza del puerto')->assertSee('Cerámica');
    $this->get($product->imagen_url)->assertOk();
});

test('product editing is owner protected and replacing images updates the marketplace main image', function () {
    $shop = createShopFor($this->seller);
    $other = createShopFor($this->buyer);
    $category = Categoria::create(['nombre_categoria' => 'Cerámica']);
    $product = $shop->productos()->create(['nombre_producto' => 'Taza', 'codigo_producto' => 'MINE', 'id_categoria' => $category->getKey(), 'precio' => 100]);
    $foreign = $other->productos()->create(['nombre_producto' => 'Producto ajeno', 'codigo_producto' => 'OTHER', 'id_categoria' => $category->getKey(), 'precio' => 999]);
    $product->imagenes()->create(['imagen_url' => '/legacy.jpg', 'es_principal' => true, 'orden' => 0]);
    $data = ['nombre_producto' => 'Taza actualizada', 'descripcion' => 'Nueva descripción', 'id_categoria' => $category->getKey(), 'precio' => '225.25', 'imagen' => UploadedFile::fake()->image('taza.png', 300, 300), 'id_tienda' => $other->getKey()];
    $this->actingAs($this->seller)->get(route('vendedor.productos.edit', $foreign))->assertForbidden();
    $this->put(route('vendedor.productos.update', $foreign), $data)->assertForbidden();
    $this->put(route('vendedor.productos.update', $product), $data)->assertSessionHasNoErrors();
    expect($product->fresh()->id_tienda)->toBe($shop->getKey())->and($product->fresh()->imagenPrincipal->imagen_url)->toBe($product->fresh()->imagen_url)->and($foreign->fresh()->precio)->toBe(999.0);
    expect($product->fresh()->estado_moderacion)->toBe('aprobada');
    $this->get(route('productos.show', $product))->assertOk();
    $product->forceFill(['estado_moderacion' => 'aprobada'])->save();
    $this->get(route('productos.show', $product))->assertSee($product->fresh()->imagen_url, false);
    $this->get(route('vendedor.productos.index', ['q' => 'ajeno']))->assertViewHas('productos', fn ($items) => $items->isEmpty());
});

test('summary metrics and popular products reflect only the current store activity', function () {
    $shop = createShopFor($this->seller);
    $other = createShopFor($this->buyer);
    $category = Categoria::create(['nombre_categoria' => 'Cerámica']);
    $mine = $shop->productos()->create(['nombre_producto' => 'Mi taza', 'codigo_producto' => 'OWN', 'id_categoria' => $category->getKey(), 'precio' => 100]);
    $foreign = $other->productos()->create(['nombre_producto' => 'Ajeno', 'codigo_producto' => 'FOREIGN', 'id_categoria' => $category->getKey(), 'precio' => 900]);
    $this->buyer->productosFavoritos()->attach([$mine->getKey(), $foreign->getKey()]);
    $this->buyer->tiendasFavoritas()->attach([$shop->getKey(), $other->getKey()]);
    $shop->opiniones()->create(['id_usuario' => $this->buyer->id, 'calificacion' => 4, 'comentario' => 'Buena tienda']);
    $mine->opiniones()->create(['id_usuario' => $this->buyer->id, 'calificacion' => 5, 'comentario' => 'Buena taza']);
    $response = $this->actingAs($this->seller)->get(route('vendedor.dashboard'))->assertOk()->assertSee('4.0 / 5')->assertSee('Mi taza')->assertDontSee('Ajeno');
    expect($response->viewData('favorites'))->toBe(1)->and($response->viewData('productFavorites'))->toBe(1)->and($response->viewData('productReviews'))->toBe(1)->and($response->viewData('tienda')->productos_count)->toBe(1)->and($response->viewData('categorias')->first()->productos_count)->toBe(1)->and($response->viewData('populares')->first()->favoritos_count)->toBe(1);
});

test('public store media cannot expose seller documents or unreferenced files', function () {
    $shop = createShopFor($this->seller);
    Storage::disk('local')->put('tiendas/'.$shop->getKey().'/abcdef.png', 'Not actually an image');
    Storage::disk('local')->put('vendedores/private.pdf', 'Private document');
    $this->get('/media/tiendas/'.$shop->getKey().'/abcdef.png')->assertNotFound();
    $shop->update(['logo_tienda' => '/media/tiendas/'.$shop->getKey().'/abcdef.png']);
    $this->get($shop->logo_tienda)->assertStatus(415);
    $this->get('/media/tiendas/'.$shop->getKey().'/private.pdf')->assertNotFound();
});
