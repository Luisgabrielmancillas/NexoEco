<?php

use App\Models\Apartado;
use App\Models\Categoria;
use App\Models\ProductChat;
use App\Models\TiposUsuario;
use App\Models\User;
use App\Services\LocalDiscovery;
use App\Services\MercadoPagoReservations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Http::preventStrayRequests();
    $buyerRole = TiposUsuario::create(['nombre_tipo' => 'comprador']);
    $this->sellerRole = TiposUsuario::create(['nombre_tipo' => 'vendedor']);
    $this->buyer = User::create(['name' => 'Ana', 'email' => 'ana@discovery.test', 'password' => 'test-password', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer->forceFill(['ubicacion_comprador' => ['ciudad' => 'Manzanillo', 'estado' => 'Colima', 'latitud' => 19.0522, 'longitud' => -104.3158]])->save();
    $this->seller = User::create(['name' => 'Luis', 'email' => 'luis@discovery.test', 'password' => 'test-password', 'email_verified_at' => now(), 'activo' => true]);
    $this->other = User::create(['name' => 'Otra persona', 'email' => 'other@discovery.test', 'password' => 'test-password', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer->tipos_usuario()->attach($buyerRole);
    $this->other->tipos_usuario()->attach($buyerRole);
    $this->seller->tipos_usuario()->attach($this->sellerRole);
    $this->store = $this->seller->tiendas()->create(['nombre_tienda' => 'Taller cercano', 'ciudad' => 'Manzanillo', 'estado' => 'Colima', 'direccion' => 'Calle del taller 10', 'latitud' => 19.053, 'longitud' => -104.316]);
    $category = Categoria::create(['nombre_categoria' => 'Artesanía']);
    $this->product = $this->store->productos()->create(['nombre_producto' => 'Taza del taller', 'codigo_producto' => 'DISCOVERY-01', 'id_categoria' => $category->getKey(), 'precio' => 200]);
});

test('incoming messages notify only the recipient and reading a chat clears only its messages', function () {
    $this->actingAs($this->buyer)->post(route('chat.start', $this->product))->assertRedirect();
    $chat = ProductChat::firstOrFail();
    $this->postJson(route('chat.send', $chat), ['body' => '¿Sigue disponible?'])->assertOk();
    expect($this->seller->unreadNotifications()->count())->toBe(1)
        ->and($this->buyer->notifications()->count())->toBe(0)->and($this->other->notifications()->count())->toBe(0);
    $notification = $this->seller->notifications()->first();
    expect($notification->data['chat_id'])->toBe($chat->id)->and($notification->data['url'])->toBe(route('chat.show', $chat));
    $this->actingAs($this->seller)->get(route('vendedor.dashboard'))->assertOk()->assertSee('Notificaciones: 1 sin leer')->assertSee(route('vendedor.notificaciones'));
    $this->getJson(route('comprador.notificaciones.count', ['details' => 1]))->assertOk()->assertJsonPath('latest.title', 'Nuevo mensaje de Ana');
    $this->get(route('vendedor.notificaciones'))->assertOk()->assertSee('Nuevo mensaje de Ana')->assertDontSee('Navegación del comprador');
    $this->postJson(route('chat.send', $chat), ['body' => 'Sí, puedes recogerla hoy.'])->assertOk();
    $this->actingAs($this->other)->post(route('chat.start', $this->product));
    $second = ProductChat::latest('id')->first();
    $this->postJson(route('chat.send', $second), ['body' => 'Quisiera una taza.'])->assertOk();
    $this->actingAs($this->seller)->getJson(route('chat.messages', $chat))->assertOk();
    expect($notification->fresh()->read_at)->not->toBeNull()
        ->and($this->seller->unreadNotifications()->count())->toBe(1)
        ->and($this->buyer->unreadNotifications()->count())->toBe(1);
    $this->actingAs($this->buyer)->getJson(route('chat.messages', $chat))->assertOk();
    expect($this->buyer->unreadNotifications()->count())->toBe(0);
});

test('reservation confirmation notifies both sides once even when the payment is reconciled repeatedly', function () {
    $reservation = Apartado::create(['id_producto' => $this->product->getKey(), 'buyer_id' => $this->buyer->id, 'seller_id' => $this->seller->id, 'producto_nombre' => $this->product->nombre_producto, 'monto' => 50, 'moneda' => 'MXN', 'merchant_id' => '123', 'live_mode' => false, 'condiciones' => 'Recoger esta semana.']);
    expect($this->seller->notifications()->count())->toBe(1)->and($this->seller->notifications()->first()->data['titulo'])->toBe('Nueva solicitud de apartado');
    $payments = app(MercadoPagoReservations::class);
    $payment = ['id' => 456, 'external_reference' => $reservation->id, 'collector_id' => 123, 'currency_id' => 'MXN', 'transaction_amount' => 50, 'status' => 'approved', 'live_mode' => false];
    DB::transaction(fn () => $payments->applyPayment($reservation, $payment));
    DB::transaction(fn () => $payments->applyPayment($reservation->fresh(), $payment));
    expect($this->seller->notifications()->count())->toBe(2)->and($this->buyer->notifications()->count())->toBe(1)
        ->and($this->buyer->notifications()->first()->data['titulo'])->toBe('Anticipo confirmado')
        ->and($this->buyer->notifications()->first()->data['url'])->toBe(route('apartados.index'));
    $this->actingAs($this->other)->getJson(route('comprador.notificaciones.count', ['details' => 1]))->assertJsonPath('unread', 0)->assertJsonPath('latest', null);
    $payment['status'] = 'refunded';
    $payments->applyPayment($reservation->fresh(), $payment);
    expect($this->buyer->notifications()->count())->toBe(2)->and($this->seller->notifications()->count())->toBe(3);
});

test('notifications roll back with a failed chat transaction', function () {
    $this->actingAs($this->buyer)->post(route('chat.start', $this->product));
    $chat = ProductChat::firstOrFail();
    try {
        DB::transaction(function () use ($chat) {
            $chat->conversation->messages()->create(['user_id' => $this->buyer->id, 'body' => 'Mensaje fallido']);
            throw new RuntimeException('Rollback');
        });
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Rollback');
    }
    expect($chat->conversation->messages()->count())->toBe(0)->and($this->seller->notifications()->count())->toBe(0);
});

test('nearby stores are ordered by real distance and exclude distant missing and inactive locations', function () {
    $medium = $this->seller->tiendas()->create(['nombre_tienda' => 'Tienda a dos kilómetros', 'latitud' => 19.07, 'longitud' => -104.3158]);
    $this->seller->tiendas()->create(['nombre_tienda' => 'Tienda lejana', 'latitud' => 19.243, 'longitud' => -103.724]);
    $this->seller->tiendas()->create(['nombre_tienda' => 'Sin punto guardado']);
    $this->other->tiendas()->create(['nombre_tienda' => 'Vendedor sin autorizar', 'latitud' => 19.0522, 'longitud' => -104.3158]);
    $response = $this->actingAs($this->buyer)->get(route('comprador.dashboard'))->assertOk()->assertSee('Tiendas cerca de ti')->assertSee('Cómo llegar');
    expect($response->viewData('tiendasCercanas')->modelKeys())->toBe([$this->store->getKey(), $medium->getKey()])
        ->and($response->viewData('tiendasCercanas')->last()->distance_km)->toBeGreaterThan(1.9)->toBeLessThan(2.1);
    $this->seller->update(['activo' => false]);
    $this->get(route('comprador.dashboard'))->assertViewHas('tiendasCercanas', fn ($stores) => $stores->isEmpty());
});

test('saved locations remain private and changing the point refreshes nearby recommendations', function () {
    $this->actingAs($this->other)->get(route('comprador.dashboard'))->assertViewHas('tiendasCercanas', fn ($stores) => $stores->isEmpty())->assertSee('Elegir ubicación en el mapa')->assertDontSee('data-lat="19.0522"', false);
    $this->actingAs($this->buyer)->postJson(route('marketplace.ubicacion.update'), ['ciudad' => 'Colima', 'estado' => 'Colima', 'latitud' => 19.243, 'longitud' => -103.724])->assertOk();
    $this->get(route('comprador.dashboard'))->assertViewHas('tiendasCercanas', fn ($stores) => $stores->isEmpty());
    $this->get(route('tiendas.show', $this->store))->assertSee('Esta tienda está lejos del punto guardado.')->assertSee('Cambiar ubicación guardada');
    $this->buyer->forceFill(['ubicacion_comprador' => ['ciudad' => 'Manzanillo', 'estado' => 'Colima']])->save();
    $this->get(route('comprador.dashboard'))->assertSee('Añade el punto exacto en el mapa')->assertViewHas('ubicacionMapa', null);
});

test('Google Maps directions use the saved buyer point and the actual store point', function () {
    $url = app(LocalDiscovery::class)->directionsUrl($this->store, $this->buyer->ubicacion_comprador);
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    expect($params)->toBe(['api' => '1', 'destination' => '19.053,-104.316', 'travelmode' => 'driving', 'origin' => '19.0522,-104.3158']);
    $this->actingAs($this->buyer)->get(route('productos.show', $this->product))->assertOk()->assertSee('Ver ruta y cómo llegar')->assertSee('Abrir en Google Maps');
    $this->store->update(['latitud' => null, 'longitud' => null]);
    expect(app(LocalDiscovery::class)->directionsUrl($this->store, null))->toContain('Calle%20del%20taller%2010');
});

test('route endpoint uses account coordinates instead of untrusted origins and returns road geometry and directions', function () {
    Http::fake(['https://router.project-osrm.org/*' => Http::response([
        'code' => 'Ok', 'routes' => [[
            'distance' => 340, 'duration' => 120, 'geometry' => ['type' => 'LineString', 'coordinates' => [[-104.3158, 19.0522], [-104.316, 19.053]]],
            'legs' => [['steps' => [
                ['distance' => 200, 'name' => 'Avenida México', 'maneuver' => ['type' => 'depart']],
                ['distance' => 140, 'name' => 'Calle del taller', 'maneuver' => ['type' => 'turn', 'modifier' => 'right']],
                ['distance' => 0, 'name' => '', 'maneuver' => ['type' => 'arrive']],
            ]]],
        ]],
    ])]);
    $this->actingAs($this->buyer)->getJson(route('tiendas.ruta', ['tienda' => $this->store, 'latitud' => 0, 'longitud' => 0, 'id_usuario' => $this->other->id]))
        ->assertOk()->assertJsonPath('origin', [19.0522, -104.3158])->assertJsonPath('distance', 340)
        ->assertJsonPath('steps.1.instruction', 'Gira a la derecha por Calle del taller.')->assertJsonPath('geometry.type', 'LineString')
        ->assertHeader('Cache-Control', 'no-store, private');
    Http::assertSent(fn ($request) => str_contains($request->url(), '/driving/-104.3158,19.0522;-104.316,19.053') && $request['steps'] === 'true');
    $this->actingAs($this->other)->getJson(route('tiendas.ruta', $this->store))->assertUnprocessable();
    Http::assertSentCount(1);
});

test('routing outages have a usable Google Maps fallback and route access requires a verified account', function () {
    $this->getJson(route('tiendas.ruta', $this->store))->assertUnauthorized();
    $this->buyer->forceFill(['email_verified_at' => null])->save();
    $this->actingAs($this->buyer)->getJson(route('tiendas.ruta', $this->store))->assertForbidden();
    Http::assertNothingSent();
    $this->buyer->forceFill(['email_verified_at' => now()])->save();
    Http::fake(['https://router.project-osrm.org/*' => Http::response(['code' => 'NoRoute'], 200)]);
    $this->getJson(route('tiendas.ruta', $this->store))->assertStatus(503)->assertJsonPath('message', 'No pudimos calcular la ruta en este momento. Puedes consultar cómo llegar en Google Maps.');
    $this->get(route('tiendas.show', $this->store))->assertOk()->assertSee('https://www.google.com/maps/dir/?api=1', false);
});
