<?php

use App\Models\Apartado;
use App\Models\Categoria;
use App\Models\CommercialOffer;
use App\Models\ProductChat;
use App\Models\SellerAutomation;
use App\Models\SellerQuickReply;
use App\Models\SellerStoreDesign;
use App\Models\SellerSubscription;
use App\Models\StoreUpdate;
use App\Models\TiposUsuario;
use App\Models\User;
use App\Services\SellerGrowthScheduler;
use App\Services\StoreAudience;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Http::preventStrayRequests();
    Storage::fake('local');
    $this->travelTo(Carbon::parse('2026-10-12 10:00:00', 'America/Mexico_City')->utc());
    foreach (['comprador', 'vendedor'] as $role) {
        TiposUsuario::create(['nombre_tipo' => $role]);
    }
    $this->seller = User::create(['name' => 'Vendedor', 'email' => 'growth-seller@example.test', 'password' => 'password', 'email_verified_at' => now(), 'activo' => true]);
    $this->other = User::create(['name' => 'Otro vendedor', 'email' => 'growth-other@example.test', 'password' => 'password', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer = User::create(['name' => 'Ana', 'email' => 'growth-buyer@example.test', 'password' => 'password', 'email_verified_at' => now(), 'activo' => true]);
    foreach ([$this->seller, $this->other] as $seller) {
        $seller->tipos_usuario()->attach(TiposUsuario::whereIn('nombre_tipo', ['comprador', 'vendedor'])->pluck('id_tipo_usuario'));
    }
    $this->buyer->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'comprador')->value('id_tipo_usuario'));
    $this->shop = $this->seller->tiendas()->create(['nombre_tienda' => 'Taller del puerto']);
    $this->otherShop = $this->other->tiendas()->create(['nombre_tienda' => 'Tienda ajena']);
    $this->category = Categoria::create(['nombre_categoria' => 'Cerámica']);
    $this->product = growthProduct($this, 'Taza artesanal');
});

function growthProduct($test, string $name, $shop = null)
{
    return ($shop ?? $test->shop)->productos()->create(['nombre_producto' => $name, 'codigo_producto' => fake()->uuid(), 'id_categoria' => $test->category->getKey(), 'descripcion' => 'Hecho en nuestra comunidad.', 'precio' => 200, 'apartados_activos' => true, 'apartado_monto' => 50, 'apartado_condiciones' => 'Recoger en tienda.']);
}

function growthPlan($test, string $plan = 'impulso'): SellerSubscription
{
    return SellerSubscription::create(['user_id' => $test->seller->id, 'plan' => $plan, 'amount' => config('subscriptions.plans.'.$plan.'.price'), 'status' => 'authorized', 'paid_until' => now()->addMonthNoOverflow()]);
}

function growthChat($test): ProductChat
{
    $test->actingAs($test->buyer)->post(route('chat.start', $test->product))->assertRedirect();

    return ProductChat::where('id_producto', $test->product->getKey())->where('buyer_id', $test->buyer->id)->firstOrFail();
}

function growthReservation($test): Apartado
{
    return Apartado::create(['id_producto' => $test->product->getKey(), 'buyer_id' => $test->buyer->id, 'seller_id' => $test->seller->id, 'producto_nombre' => $test->product->nombre_producto, 'monto' => 50, 'moneda' => 'MXN', 'merchant_id' => '123', 'condiciones' => 'Recoger en tienda.', 'estado' => 'pagado']);
}

function growthOffer($test, array $overrides = []): array
{
    return array_replace(['id_producto' => $test->product->getKey(), 'title' => 'Oferta de fin de semana', 'description' => 'Pregunta por disponibilidad.', 'discount_percent' => 15, 'enabled' => 1], $overrides);
}

test('plan comparison exposes every agreed benefit while core seller tools remain free', function () {
    $this->actingAs($this->seller)->get(route('vendedor.suscripcion.index'))->assertOk()->assertSee('Para negocios que buscan más clientes.')->assertSee('Para negocios que buscan crecer y profesionalizarse.')->assertSee('Compara todos los beneficios')->assertSee('Reportes descargables')->assertSee('3 activas')->assertSee('Ilimitadas');
    foreach (['vendedor.herramientas.index', 'vendedor.promociones.index', 'vendedor.estadisticas.index', 'vendedor.novedades.index', 'vendedor.calendario'] as $route) {
        $this->get(route($route))->assertOk();
        $this->actingAs($this->buyer)->get(route($route))->assertForbidden();
        $this->actingAs($this->seller);
    }
    $this->post(route('vendedor.herramientas.respuestas.store'), ['title' => 'Entrega', 'body' => 'Recoger en tienda.'])->assertSessionHasErrors('plan');
    $this->assertDatabaseCount('seller_quick_replies', 0);
});

test('impulso enforces five quick replies and editing deletion and privacy are owner scoped', function () {
    $plan = growthPlan($this);
    $this->actingAs($this->seller);
    for ($i = 1; $i <= 5; $i++) {
        $this->post(route('vendedor.herramientas.respuestas.store'), ['title' => 'Respuesta '.$i, 'body' => 'Hola, {comprador}.'])->assertSessionHasNoErrors();
    }
    $this->post(route('vendedor.herramientas.respuestas.store'), ['title' => 'Sexta', 'body' => 'Extra'])->assertSessionHasErrors('respuestas');
    $reply = SellerQuickReply::firstOrFail();
    $this->put(route('vendedor.herramientas.respuestas.update', $reply), ['title' => 'Actualizada', 'body' => 'Nuevo mensaje'])->assertSessionHasNoErrors();
    $this->get(route('vendedor.herramientas.index'))->assertOk()->assertSee('Actualizada');
    $this->actingAs($this->other)->put(route('vendedor.herramientas.respuestas.update', $reply), ['title' => 'Intrusión', 'body' => 'No'])->assertNotFound();
    $this->delete(route('vendedor.herramientas.respuestas.destroy', $reply))->assertNotFound();
    $plan->update(['paid_until' => now()->subSecond()]);
    $this->actingAs($this->seller)->delete(route('vendedor.herramientas.respuestas.destroy', $reply))->assertSessionHasNoErrors();
    expect(SellerQuickReply::count())->toBe(4);
});

test('pro permits more than five quick replies and inserts them only as seller drafts', function () {
    growthPlan($this, 'pro');
    for ($i = 1; $i <= 7; $i++) {
        $this->actingAs($this->seller)->post(route('vendedor.herramientas.respuestas.store'), ['title' => 'Respuesta '.$i, 'body' => 'Hola, {comprador}. Consulta {producto}.'])->assertSessionHasNoErrors();
    }
    $chat = growthChat($this);
    $this->actingAs($this->seller)->get(route('chat.show', $chat))->assertOk()->assertSee('Hola, Ana. Consulta Taza artesanal.')->assertSee('data-quick-reply', false);
    expect($chat->messages()->count())->toBe(0);
    $this->actingAs($this->buyer)->get(route('chat.show', $chat))->assertOk()->assertDontSee('data-quick-reply', false)->assertDontSee('Estado de la negociación');
});

test('all growth screens render the active benefits and editable commercial offer for both plans', function (string $plan) {
    growthPlan($this, $plan);
    SellerAutomation::create(['user_id' => $this->seller->id, 'kind' => 'welcome', 'body' => 'Hola, {comprador}.', 'enabled' => true]);
    $this->actingAs($this->seller)->post(route('vendedor.promociones.store'), growthOffer($this))->assertSessionHasNoErrors();
    foreach (['vendedor.herramientas.index', 'vendedor.promociones.index', 'vendedor.estadisticas.index', 'vendedor.novedades.index', 'vendedor.calendario'] as $route) {
        $this->get(route($route))->assertOk();
    }
    $this->get(route('vendedor.promociones.index'))->assertOk()->assertSee('Editar promoción')->assertSee('Oferta de fin de semana');
})->with(['impulso', 'pro']);

test('welcome automation sends once on new chats and pauses when the plan expires', function () {
    $plan = growthPlan($this);
    $this->actingAs($this->seller)->put(route('vendedor.herramientas.automatizaciones'), ['kind' => 'welcome', 'enabled' => 1, 'body' => 'Hola, {comprador}. Gracias por preguntar por {producto}.'])->assertSessionHasNoErrors();
    $this->get(route('vendedor.herramientas.index'))->assertOk()->assertSee('Mensaje de bienvenida');
    $chat = growthChat($this);
    $this->post(route('chat.start', $this->product))->assertRedirect();
    expect($chat->messages()->count())->toBe(1)->and($chat->messages()->first()->body)->toContain('Mensaje automático', 'Hola, Ana', 'Taza artesanal');
    $this->actingAs($this->seller)->put(route('vendedor.herramientas.automatizaciones'), ['kind' => 'away', 'enabled' => 1, 'body' => 'No estamos.', 'hours' => ['days' => [1], 'start' => '09:00', 'end' => '18:00']])->assertSessionHasErrors('plan');
    $plan->update(['paid_until' => now()->subSecond()]);
    $newProduct = growthProduct($this, 'Otra taza');
    $this->actingAs($this->buyer)->post(route('chat.start', $newProduct))->assertRedirect();
    expect(ProductChat::where('id_producto', $newProduct->getKey())->first()->messages()->count())->toBe(0);
});

test('pro out of hours messages respect overnight opening hours and deduplicate daily replies', function () {
    growthPlan($this, 'pro');
    $this->actingAs($this->seller)->put(route('vendedor.herramientas.automatizaciones'), ['kind' => 'away', 'enabled' => 1, 'body' => 'Volvemos pronto.', 'hours' => ['days' => [1], 'start' => '22:00', 'end' => '02:00']])->assertSessionHasNoErrors();
    $chat = growthChat($this);
    $this->travelTo(Carbon::parse('2026-10-12 23:00:00', 'America/Mexico_City')->utc());
    $this->postJson(route('chat.send', $chat), ['body' => 'Primer mensaje'])->assertOk();
    $this->travelTo(Carbon::parse('2026-10-13 01:00:00', 'America/Mexico_City')->utc());
    $this->postJson(route('chat.send', $chat), ['body' => 'Segundo mensaje'])->assertOk();
    expect($chat->messages()->count())->toBe(2);
    $this->travelTo(Carbon::parse('2026-10-13 03:00:00', 'America/Mexico_City')->utc());
    $this->postJson(route('chat.send', $chat), ['body' => 'Fuera de horario'])->assertOk();
    $this->postJson(route('chat.send', $chat), ['body' => 'Otra pregunta'])->assertOk();
    expect($chat->messages()->count())->toBe(5)->and(DB::table('chat_automation_deliveries')->where('kind', 'away')->count())->toBe(1);
});

test('negotiations are free private seller declarations and never mutate verified payments', function () {
    $chat = growthChat($this);
    $reservation = growthReservation($this);
    $this->actingAs($this->seller)->put(route('vendedor.mensajes.negociacion', $chat), ['negotiation_status' => 'concretada', 'reported_sale_amount' => 200, 'negotiation_notes' => 'Nota privada: entrega en mostrador.', 'estado' => 'reembolsado'])->assertSessionHasNoErrors();
    expect($chat->fresh()->reported_sale_amount)->toBe('200.00')->and($chat->fresh()->sale_reported_at)->not->toBeNull()->and($reservation->fresh()->estado)->toBe('pagado');
    $this->get(route('vendedor.mensajes.index', ['estado' => 'concretada']))->assertOk()->assertSee('Venta concretada');
    $this->get(route('vendedor.mensajes.index', ['estado' => 'nuevo']))->assertOk()->assertViewHas('chats', fn ($c) => $c->isEmpty());
    $this->actingAs($this->buyer)->get(route('chat.show', $chat))->assertOk()->assertDontSee('Nota privada: entrega en mostrador.');
    $this->put(route('vendedor.mensajes.negociacion', $chat), ['negotiation_status' => 'no_concretada'])->assertForbidden();
    $this->actingAs($this->other)->put(route('vendedor.mensajes.negociacion', $chat), ['negotiation_status' => 'no_concretada'])->assertNotFound();
    $this->actingAs($this->seller)->put(route('vendedor.mensajes.negociacion', $chat), ['negotiation_status' => 'negociacion', 'reported_sale_amount' => 500])->assertSessionHasNoErrors();
    expect($chat->fresh()->reported_sale_amount)->toBeNull()->and($chat->fresh()->sale_reported_at)->toBeNull();
});

test('product pins enforce each plan limit idempotency and ownership and lead the store catalog', function (string $plan, int $limit) {
    growthPlan($this, $plan);
    $products = collect(range(1, $limit + 1))->map(fn ($n) => growthProduct($this, 'Fijado '.$n));
    $this->actingAs($this->seller);
    foreach ($products->take($limit) as $product) {
        $this->post(route('vendedor.herramientas.fijados', $product), ['pinned' => 1])->assertSessionHasNoErrors();
    }
    $this->post(route('vendedor.herramientas.fijados', $products->first()), ['pinned' => 1])->assertSessionHasNoErrors();
    $this->post(route('vendedor.herramientas.fijados', $products->last()), ['pinned' => 1])->assertSessionHasErrors('fijados');
    expect(DB::table('product_pins')->count())->toBe($limit);
    $this->get(route('tiendas.show', $this->shop))->assertOk()->assertSee('Fijado por la tienda')->assertViewHas('productos', fn ($p) => $p->first()->is_pinned === 1);
    $foreign = growthProduct($this, 'Ajeno', $this->otherShop);
    $this->post(route('vendedor.herramientas.fijados', $foreign), ['pinned' => 1])->assertNotFound();
    $this->post(route('vendedor.herramientas.fijados', $products->first()), ['pinned' => 0])->assertSessionHasNoErrors();
    $this->post(route('vendedor.herramientas.fijados', $products->last()), ['pinned' => 1])->assertSessionHasNoErrors();
})->with([['impulso', 3], ['pro', 10]]);

test('impulso commercial offers enforce three active offers and preserve price and reservation terms', function () {
    growthPlan($this);
    $this->actingAs($this->seller);
    foreach ([$this->product, growthProduct($this, 'Plato'), growthProduct($this, 'Jarrón')] as $product) {
        $this->post(route('vendedor.promociones.store'), growthOffer($this, ['id_producto' => $product->getKey(), 'user_id' => $this->other->id]))->assertSessionHasNoErrors();
    }
    $extra = growthProduct($this, 'Cuarto producto');
    $this->post(route('vendedor.promociones.store'), growthOffer($this, ['id_producto' => $extra->getKey()]))->assertSessionHasErrors('oferta');
    $first = CommercialOffer::firstOrFail();
    expect($first->user_id)->toBe($this->seller->id)->and($this->product->fresh()->precio)->toBe(200.0)->and($this->product->fresh()->apartado_monto)->toBe('50.00')->and($this->product->fresh()->apartado_condiciones)->toBe('Recoger en tienda.');
    $this->get(route('productos.show', $this->product))->assertOk()->assertSee('15% de descuento')->assertSee('Oferta informativa.');
    $this->actingAs($this->other)->delete(route('vendedor.promociones.destroy', $first))->assertNotFound();
    $this->actingAs($this->seller)->delete(route('vendedor.promociones.destroy', $first))->assertSessionHasNoErrors();
    $this->post(route('vendedor.promociones.store'), growthOffer($this, ['id_producto' => $extra->getKey()]))->assertSessionHasNoErrors();
    $this->post(route('vendedor.promociones.store'), growthOffer($this, ['starts_at' => now('America/Mexico_City')->addDay()->format('Y-m-d\TH:i')]))->assertSessionHasErrors('starts_at');
});

test('pro schedules unlimited offers with nonoverlapping dates and automatic visibility windows', function () {
    growthPlan($this, 'pro');
    $this->actingAs($this->seller);
    $start = now('America/Mexico_City')->addDay();
    $end = $start->copy()->addDay();
    $data = growthOffer($this, ['starts_at' => $start->format('Y-m-d\TH:i'), 'ends_at' => $end->format('Y-m-d\TH:i')]);
    $this->post(route('vendedor.promociones.store'), $data)->assertSessionHasNoErrors();
    $this->post(route('vendedor.promociones.store'), $data)->assertSessionHasErrors('id_producto');
    foreach (range(1, 4) as $n) {
        $product = growthProduct($this, 'Pro producto '.$n);
        $this->post(route('vendedor.promociones.store'), growthOffer($this, ['id_producto' => $product->getKey()]))->assertSessionHasNoErrors();
    }
    $this->get(route('productos.show', $this->product))->assertOk()->assertDontSee('Oferta de fin de semana');
    $this->travelTo($start->copy()->utc()->addMinute());
    $this->get(route('productos.show', $this->product))->assertOk()->assertSee('Oferta de fin de semana');
    $this->travelTo($end->copy()->utc()->addMinute());
    $this->get(route('productos.show', $this->product))->assertOk()->assertDontSee('Oferta de fin de semana');
});

test('commercial offers notify followers once and editing or scheduler retries do not repeat notices', function () {
    growthPlan($this);
    $this->buyer->followedStores()->attach($this->shop->getKey());
    $this->actingAs($this->seller)->post(route('vendedor.promociones.store'), growthOffer($this))->assertSessionHasNoErrors();
    $offer = CommercialOffer::firstOrFail();
    expect($offer->notified_at)->not->toBeNull();
    expect($this->buyer->notifications()->where('data->event', 'store.offer')->count())->toBe(1);
    expect($this->other->notifications()->count())->toBe(0);
    $this->put(route('vendedor.promociones.update', $offer), growthOffer($this, ['title' => 'Oferta actualizada']))->assertSessionHasNoErrors();
    app(StoreAudience::class)->offerPublished($offer);
    app(SellerGrowthScheduler::class)->run();
    expect($this->buyer->notifications()->where('data->event', 'store.offer')->count())->toBe(1);
    $this->buyer->followedStores()->detach($this->shop->getKey());
    $product = growthProduct($this, 'Nuevo producto');
    $this->post(route('vendedor.promociones.store'), growthOffer($this, ['id_producto' => $product->getKey()]))->assertSessionHasNoErrors();
    expect($this->buyer->notifications()->where('data->event', 'store.offer')->count())->toBe(1);
});

test('scheduled offer notices wait for their start and skip expired plans disabled offers and removed products', function () {
    $plan = growthPlan($this, 'pro');
    $this->buyer->followedStores()->attach($this->shop->getKey());
    $start = now('America/Mexico_City')->addHour();
    $this->actingAs($this->seller)->post(route('vendedor.promociones.store'), growthOffer($this, ['starts_at' => $start->format('Y-m-d\TH:i')]))->assertSessionHasNoErrors();
    $scheduled = CommercialOffer::firstOrFail();
    foreach (['disabled', 'deleted'] as $state) {
        $product = growthProduct($this, $state);
        $this->post(route('vendedor.promociones.store'), growthOffer($this, ['id_producto' => $product->getKey(), 'starts_at' => $start->format('Y-m-d\TH:i'), 'enabled' => $state === 'disabled' ? 0 : 1]))->assertSessionHasNoErrors();
        if ($state === 'deleted') {
            $product->delete();
        }
    }
    app(SellerGrowthScheduler::class)->run();
    expect($this->buyer->notifications()->count())->toBe(0);
    $this->travelTo($start->copy()->utc()->addMinute());
    app(SellerGrowthScheduler::class)->run();
    app(SellerGrowthScheduler::class)->run();
    expect($this->buyer->notifications()->where('data->event', 'store.offer')->count())->toBe(1);
    expect($scheduled->fresh()->notified_at)->not->toBeNull();
    expect(CommercialOffer::whereNotNull('notified_at')->count())->toBe(1);
    $product = growthProduct($this, 'Plan por vencer');
    $this->post(route('vendedor.promociones.store'), growthOffer($this, ['id_producto' => $product->getKey(), 'starts_at' => now('America/Mexico_City')->addHour()->format('Y-m-d\TH:i')]))->assertSessionHasNoErrors();
    $plan->update(['paid_until' => now()->subSecond()]);
    $this->travel(2)->hours();
    app(SellerGrowthScheduler::class)->run();
    expect($this->buyer->notifications()->where('data->event', 'store.offer')->count())->toBe(1);
});

test('store customization separates basic and advanced options and expiry restores the standard appearance', function () {
    $plan = growthPlan($this);
    $this->actingAs($this->seller)->put(route('vendedor.herramientas.personalizacion'), ['theme' => 'bosque', 'tagline' => 'Creado en el puerto', 'layout' => 'clasico'])->assertSessionHasNoErrors();
    $this->get(route('tiendas.show', $this->shop))->assertOk()->assertSee('store-theme-bosque')->assertSee('Creado en el puerto');
    $this->put(route('vendedor.herramientas.personalizacion'), ['theme' => 'oceano', 'layout' => 'compacto', 'story' => 'Historia privada del taller.'])->assertSessionHasErrors('personalizacion');
    $plan->update(['plan' => 'pro']);
    $this->put(route('vendedor.herramientas.personalizacion'), ['theme' => 'oceano', 'layout' => 'compacto', 'story' => 'Historia de nuestro taller.'])->assertSessionHasNoErrors();
    $this->get(route('tiendas.show', $this->shop))->assertOk()->assertSee('store-layout-compact')->assertSee('Historia de nuestro taller.');
    $plan->update(['paid_until' => now()->subSecond()]);
    $this->get(route('tiendas.show', $this->shop))->assertOk()->assertDontSee('Historia de nuestro taller.')->assertDontSee('store-layout-compact');
    expect(SellerStoreDesign::where('user_id', $this->seller->id)->first()->story)->toBe('Historia de nuestro taller.');
});

test('analytics deduplicate visits provide basic chat metrics and distinguish reported sales from verified deposits', function () {
    growthPlan($this);
    $this->actingAs($this->seller)->get(route('productos.show', $this->product))->assertOk();
    expect(DB::table('seller_daily_metrics')->count())->toBe(0);
    $this->actingAs($this->buyer)->get(route('productos.show', $this->product))->assertOk();
    $this->get(route('productos.show', $this->product))->assertOk();
    $this->get(route('tiendas.show', $this->shop))->assertOk();
    $chat = growthChat($this);
    $this->buyer->productosFavoritos()->attach($this->product->getKey());
    growthReservation($this);
    $this->actingAs($this->seller)->put(route('vendedor.mensajes.negociacion', $chat), ['negotiation_status' => 'concretada', 'reported_sale_amount' => 200])->assertSessionHasNoErrors();
    $this->get(route('vendedor.estadisticas.index'))->assertOk()->assertViewHas('summary', fn ($s) => $s['product_visits'] === 1 && $s['store_visits'] === 1 && $s['contacts'] === 1 && $s['contact_rate'] === 100.0 && $s['favorites'] === 1 && $s['verified_deposits'] === 50.0 && $s['reported_amount'] === 200.0)->assertSee('Anticipos verificados')->assertSee('Ventas declaradas')->assertDontSee('Descargar reporte CSV');
    $this->get(route('vendedor.estadisticas.index', ['from' => '2026-10-01']))->assertSessionHasErrors('plan');
    $this->get(route('vendedor.estadisticas.export'))->assertSessionHasErrors('plan');
});

test('pro analytics and CSV are scoped by owner and dates and neutralize spreadsheet formulas', function () {
    growthPlan($this, 'pro');
    $this->product->update(['nombre_producto' => '=HYPERLINK("https://example.test")']);
    growthProduct($this, 'Producto confidencial ajeno', $this->otherShop);
    $this->actingAs($this->buyer)->get(route('productos.show', $this->product))->assertOk();
    growthChat($this);
    $this->actingAs($this->seller)->get(route('vendedor.estadisticas.index'))->assertOk()->assertSee('Interés por producto')->assertSee('Descargar reporte CSV')->assertDontSee('Producto confidencial ajeno');
    $response = $this->get(route('vendedor.estadisticas.export'))->assertOk()->assertDownload();
    $csv = $response->streamedContent();
    expect($csv)->toContain("'=HYPERLINK", 'Anticipos confirmados vigentes', 'Ventas declaradas')->not->toContain('Producto confidencial ajeno', $this->buyer->email);
    $this->get(route('vendedor.estadisticas.index', ['from' => '2026-10-13', 'to' => '2026-10-12']))->assertSessionHasErrors('fechas');
});

test('free reservation fulfillment cannot change money or private buyer visibility and Pro gates the calendar fields', function () {
    $reservation = growthReservation($this);
    $this->actingAs($this->seller)->put(route('vendedor.apartados.seguimiento', $reservation), ['fulfillment_status' => 'completado', 'seller_notes' => 'Nota privada del vendedor', 'estado' => 'reembolsado', 'monto' => 1, 'condiciones' => 'Manipuladas'])->assertSessionHasNoErrors();
    expect($reservation->fresh()->estado)->toBe('pagado')->and($reservation->fresh()->monto)->toBe('50.00')->and($reservation->fresh()->condiciones)->toBe('Recoger en tienda.')->and($reservation->fresh()->fulfillment_status)->toBe('completado');
    $this->put(route('vendedor.apartados.seguimiento', $reservation), ['fulfillment_status' => 'pendiente', 'delivery_at' => '2026-10-13T12:00'])->assertSessionHasErrors('plan');
    $this->actingAs($this->buyer)->get(route('apartados.index'))->assertOk()->assertDontSee('Nota privada del vendedor');
    $this->actingAs($this->other)->put(route('vendedor.apartados.seguimiento', $reservation), ['fulfillment_status' => 'cancelado'])->assertNotFound();
});

test('Pro calendar and reminders use local dates and never send duplicate reminders on retries or note edits', function () {
    growthPlan($this, 'pro');
    $reservation = growthReservation($this);
    $this->actingAs($this->seller)->put(route('vendedor.apartados.seguimiento', $reservation), ['fulfillment_status' => 'pendiente', 'delivery_at' => '2026-10-13T12:00', 'follow_up_at' => '2026-10-13T10:00'])->assertSessionHasNoErrors();
    expect($reservation->fresh()->delivery_at->format('Y-m-d H:i'))->toBe('2026-10-13 18:00');
    $this->get(route('vendedor.calendario', ['month' => '2026-10']))->assertOk()->assertSee('13/10/2026 12:00')->assertViewHas('appointments', fn ($a) => $a->count() === 1);
    $this->travelTo(Carbon::parse('2026-10-13 10:01', 'America/Mexico_City')->utc());
    app(SellerGrowthScheduler::class)->run();
    app(SellerGrowthScheduler::class)->run();
    expect($this->seller->notifications()->where('data->event', 'apartado.reminder')->count())->toBe(1)->and($reservation->fresh()->reminded_at)->not->toBeNull();
    $this->put(route('vendedor.apartados.seguimiento', $reservation), ['fulfillment_status' => 'pendiente', 'seller_notes' => 'Recordar llamada', 'delivery_at' => '2026-10-13T12:00', 'follow_up_at' => '2026-10-13T10:00'])->assertSessionHasNoErrors();
    app(SellerGrowthScheduler::class)->run();
    expect($this->seller->notifications()->where('data->event', 'apartado.reminder')->count())->toBe(1)->and($reservation->fresh()->estado)->toBe('pagado');
});

test('store follows are opt in idempotent and new product notices work for free sellers until unfollowed', function () {
    $this->actingAs($this->buyer)->post(route('tiendas.follow', $this->shop), ['following' => 1])->assertSessionHasNoErrors();
    $this->post(route('tiendas.follow', $this->shop), ['following' => 1])->assertSessionHasNoErrors();
    expect(DB::table('store_follows')->count())->toBe(1);
    $this->get(route('comprador.tiendas-seguidas'))->assertOk()->assertSee('Taller del puerto');
    $this->actingAs($this->seller)->post(route('tiendas.follow', $this->shop), ['following' => 1])->assertForbidden();
    $this->post(route('vendedor.productos.store'), ['nombre_producto' => 'Nuevo producto para seguidores', 'descripcion' => 'Un producto recién llegado.', 'precio' => 100, 'id_categoria' => $this->category->getKey(), 'imagen' => UploadedFile::fake()->image('nuevo.jpg', 200, 200)])->assertSessionHasNoErrors();
    expect($this->buyer->notifications()->where('data->event', 'store.product')->count())->toBe(1);
    $this->actingAs($this->buyer)->post(route('tiendas.follow', $this->shop), ['following' => 0])->assertSessionHasNoErrors();
    app(StoreAudience::class)->productPublished($this->product);
    expect($this->buyer->notifications()->where('data->event', 'store.product')->count())->toBe(1);
});

test('Impulso publishes store updates once only to followers and Pro alone can schedule them', function () {
    growthPlan($this);
    $this->buyer->followedStores()->attach($this->shop->getKey());
    $this->actingAs($this->seller)->post(route('vendedor.novedades.store'), ['title' => 'Nueva colección', 'body' => 'Descubre las nuevas piezas.'])->assertSessionHasNoErrors();
    $update = StoreUpdate::firstOrFail();
    expect($update->status)->toBe('published')->and($this->buyer->notifications()->where('data->event', 'store.update')->count())->toBe(1)->and($this->other->notifications()->count())->toBe(0);
    app(SellerGrowthScheduler::class)->run();
    app(StoreAudience::class)->publish($update);
    expect($this->buyer->notifications()->where('data->event', 'store.update')->count())->toBe(1);
    $this->get(route('vendedor.novedades.index'))->assertOk()->assertSee('Nueva colección');
    $this->get(route('tiendas.show', $this->shop))->assertOk()->assertSee('Novedades de la tienda')->assertSee('Nueva colección');
    $this->post(route('vendedor.novedades.store'), ['title' => 'Mañana', 'body' => 'Programada', 'publish_at' => '2026-10-13T09:00'])->assertSessionHasErrors('publish_at');
    $this->actingAs($this->other)->delete(route('vendedor.novedades.destroy', $update))->assertNotFound();
    $this->actingAs($this->seller)->delete(route('vendedor.novedades.destroy', $update))->assertSessionHasNoErrors();
    expect($this->buyer->notifications()->where('data->event', 'store.update')->count())->toBe(0);
});

test('scheduled Pro updates publish on time and expired subscriptions block publication and reminders', function () {
    $plan = growthPlan($this, 'pro');
    $this->buyer->followedStores()->attach($this->shop->getKey());
    $this->actingAs($this->seller)->post(route('vendedor.novedades.store'), ['title' => 'Novedad programada', 'body' => 'Llegó el día.', 'publish_at' => '2026-10-13T09:00'])->assertSessionHasNoErrors();
    app(SellerGrowthScheduler::class)->run();
    expect(StoreUpdate::first()->status)->toBe('scheduled')->and($this->buyer->notifications()->count())->toBe(0);
    $this->travelTo(Carbon::parse('2026-10-13 09:01', 'America/Mexico_City')->utc());
    app(SellerGrowthScheduler::class)->run();
    expect(StoreUpdate::first()->status)->toBe('published')->and($this->buyer->notifications()->where('data->event', 'store.update')->count())->toBe(1);
    $this->post(route('vendedor.novedades.store'), ['title' => 'No debe publicarse', 'body' => 'Plan vencido.', 'publish_at' => '2026-10-14T09:00'])->assertSessionHasNoErrors();
    $reservation = growthReservation($this);
    $reservation->update(['follow_up_at' => now()->addHour()]);
    $plan->update(['paid_until' => now()->subSecond()]);
    $this->travelTo(Carbon::parse('2026-10-14 09:01', 'America/Mexico_City')->utc());
    app(SellerGrowthScheduler::class)->run();
    expect(StoreUpdate::where('title', 'No debe publicarse')->first()->status)->toBe('blocked')->and($this->seller->notifications()->where('data->event', 'apartado.reminder')->count())->toBe(0);
});

test('expired subscriptions hide commercial offers and pins and keep free chat and fulfillment working', function () {
    $plan = growthPlan($this, 'pro');
    $this->actingAs($this->seller)->post(route('vendedor.herramientas.fijados', $this->product), ['pinned' => 1])->assertSessionHasNoErrors();
    $this->post(route('vendedor.promociones.store'), growthOffer($this))->assertSessionHasNoErrors();
    $chat = growthChat($this);
    $reservation = growthReservation($this);
    $plan->update(['paid_until' => now()->subSecond()]);
    $this->actingAs($this->buyer)->get(route('tiendas.show', $this->shop))->assertOk()->assertDontSee('Fijado por la tienda')->assertDontSee('Oferta de fin de semana');
    $this->postJson(route('chat.send', $chat), ['body' => '¿Podemos acordar la entrega?'])->assertOk();
    $this->actingAs($this->seller)->put(route('vendedor.apartados.seguimiento', $reservation), ['fulfillment_status' => 'completado'])->assertSessionHasNoErrors();
    $this->get(route('vendedor.estadisticas.index'))->assertOk()->assertDontSee('Descargar reporte CSV')->assertDontSee('Anticipos verificados');
    $this->post(route('vendedor.promociones.store'), growthOffer($this))->assertSessionHasErrors('plan');
    $this->post(route('vendedor.herramientas.fijados', $this->product), ['pinned' => 1])->assertSessionHasErrors('plan');
    $this->post(route('vendedor.novedades.store'), ['title' => 'Sin plan', 'body' => 'No publicar.'])->assertSessionHasErrors('plan');
    expect($reservation->fresh()->estado)->toBe('pagado')->and($chat->messages()->count())->toBe(1);
});
