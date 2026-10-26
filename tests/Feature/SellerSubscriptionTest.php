<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\SellerSubscription;
use App\Models\TiposUsuario;
use App\Models\User;
use App\Services\MercadoPagoSubscriptions;
use App\Services\SellerBenefits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Http::preventStrayRequests();
    foreach (['comprador', 'vendedor'] as $role) {
        TiposUsuario::create(['nombre_tipo' => $role]);
    }
    $this->seller = User::create(['name' => 'Seller', 'email' => 'seller@example.test', 'password' => 'password', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer = User::create(['name' => 'Buyer', 'email' => 'buyer@example.test', 'password' => 'password', 'email_verified_at' => now(), 'activo' => true]);
    $this->seller->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'vendedor')->value('id_tipo_usuario'));
    $this->buyer->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'comprador')->value('id_tipo_usuario'));
    $this->shop = $this->seller->tiendas()->create(['nombre_tienda' => 'Tienda promovida', 'fecha_creacion' => now()]);
    $this->category = Categoria::create(['nombre_categoria' => 'Artesanías']);
    config(['subscriptions.access_token' => 'test-platform-token', 'subscriptions.collector_id' => '123', 'subscriptions.webhook_secret' => 'test-secret']);
});

function subscriptionProduct($test, string $name = 'Producto', $shop = null): Producto
{
    return ($shop ?? $test->shop)->productos()->create(['nombre_producto' => $name, 'descripcion' => 'Local', 'codigo_producto' => fake()->uuid(), 'id_categoria' => $test->category->getKey(), 'precio' => 50, 'fecha_publicacion' => now()]);
}

function paidSellerPlan($test, string $plan = 'impulso'): SellerSubscription
{
    return SellerSubscription::create(['user_id' => $test->seller->id, 'plan' => $plan, 'amount' => config('subscriptions.plans.'.$plan.'.price'), 'status' => 'authorized', 'provider_id' => 'abc123', 'paid_until' => now()->addMonthNoOverflow()]);
}

function fakeSubscriptionApi(SellerSubscription $subscription, string $status = 'authorized', string $paymentStatus = 'approved', array $overrides = []): void
{
    Http::swap(new \Illuminate\Http\Client\Factory);
    Http::preventStrayRequests();
    $mandate = ['id' => 'abc123', 'external_reference' => $subscription->id, 'collector_id' => 123, 'status' => $status, 'init_point' => 'https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_id=abc123', 'auto_recurring' => ['currency_id' => 'MXN', 'transaction_amount' => (float) $subscription->amount, 'frequency' => 1, 'frequency_type' => 'months']];
    Http::fake([
        'api.mercadopago.com/preapproval/search*' => Http::response(['results' => []]),
        'api.mercadopago.com/preapproval' => Http::response($mandate),
        'api.mercadopago.com/preapproval/abc123' => Http::response($mandate),
        'api.mercadopago.com/authorized_payments/search*' => Http::response(['results' => [['id' => 77, 'preapproval_id' => 'abc123', 'debit_date' => now()->toIso8601String(), 'payment' => ['id' => 456]]]]),
        'api.mercadopago.com/v1/payments/456' => Http::response(array_replace(['id' => 456, 'collector_id' => 123, 'currency_id' => 'MXN', 'transaction_amount' => (float) $subscription->amount, 'status' => $paymentStatus, 'transaction_amount_refunded' => 0], $overrides)),
    ]);
}

test('verified Pro payment unlocks navigation and paid tools immediately and expiry locks them again', function () {
    $subscription = paidSellerPlan($this, 'pro');
    $subscription->update(['status' => 'pending', 'paid_until' => null]);
    $this->actingAs($this->seller)->get(route('vendedor.dashboard'))->assertOk()->assertSee('data-plan-locked', false)->assertDontSee('alert(', false)->assertSee('Dale más visibilidad a tu tienda');

    fakeSubscriptionApi($subscription);
    $this->post(route('vendedor.suscripcion.sync', $subscription))->assertRedirect(route('vendedor.suscripcion.index'))->assertSessionHasNoErrors();
    $this->get(route('vendedor.dashboard'))->assertOk()->assertDontSee('data-plan-locked', false)->assertDontSee('Dale más visibilidad a tu tienda');
    $this->get(route('vendedor.herramientas.index'))->assertOk()->assertViewHas('subscription', fn ($plan) => $plan->id === $subscription->id && $plan->plan === 'pro');
    $this->post(route('vendedor.herramientas.respuestas.store'), ['title' => 'Entrega', 'body' => 'Puedes recoger en tienda.'])->assertSessionHasNoErrors();
    $this->get(route('vendedor.estadisticas.export'))->assertOk();

    $subscription->refresh()->update(['paid_until' => now()->subSecond()]);
    $this->get(route('vendedor.dashboard'))->assertOk()->assertSee('data-plan-locked', false)->assertSee('Dale más visibilidad a tu tienda');
    $this->post(route('vendedor.herramientas.respuestas.store'), ['title' => 'Nueva', 'body' => 'Prueba'])->assertSessionHasErrors('plan');
    Http::assertSentCount(3);
});

test('basic accounts and legacy free subscriptions never grant paid benefits or block a Pro checkout', function () {
    config(['subscriptions.access_token' => null]);
    $this->actingAs($this->seller)->post(route('vendedor.suscripcion.gratis'))->assertSessionHasNoErrors()->assertRedirect(route('vendedor.suscripcion.index'));
    $this->assertDatabaseCount('seller_subscriptions', 0);
    $legacy = SellerSubscription::create(['user_id' => $this->seller->id, 'plan' => 'gratis', 'amount' => 0, 'status' => 'authorized', 'paid_until' => now()->addYears(10)]);
    expect($legacy->entitled())->toBeFalse()->and(app(SellerBenefits::class)->subscription($this->seller))->toBeNull();
    $this->post(route('vendedor.herramientas.respuestas.store'), ['title' => 'Entrega', 'body' => 'No permite beneficios de pago.'])->assertSessionHasErrors('plan');
    $this->get(route('vendedor.suscripcion.index'))->assertOk()->assertViewHas('subscription', null);
    $this->post(route('vendedor.suscripcion.checkout'), ['plan' => 'gratis', 'acepto_renovacion' => 1])->assertSessionHasErrors('plan');

    config(['subscriptions.access_token' => 'test-platform-token']);
    // Reuse the existing checkout test's provider shape without making real requests.
    $pending = SellerSubscription::create(['user_id' => $this->seller->id, 'plan' => 'pro', 'amount' => 299, 'provider_id' => 'abc123', 'checkout_url' => 'https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_id=abc123']);
    fakeSubscriptionApi($pending, 'pending', 'pending');
    $this->post(route('vendedor.suscripcion.checkout'), ['plan' => 'pro', 'acepto_renovacion' => 1])->assertRedirect($pending->checkout_url)->assertSessionHasNoErrors();
    expect($pending->fresh()->entitled())->toBeFalse();
});

test('cancelled and paused plans preserve paid access only until the verified period ends', function (string $status) {
    $subscription = paidSellerPlan($this, 'pro');
    $subscription->update(['status' => $status]);
    $this->actingAs($this->seller)->get(route('vendedor.dashboard'))->assertOk()->assertDontSee('data-plan-locked', false);
    expect(app(SellerBenefits::class)->subscription($this->seller)?->id)->toBe($subscription->id);
    $this->travelTo($subscription->paid_until);
    expect(app(SellerBenefits::class)->subscription($this->seller))->toBeNull();
    $this->get(route('vendedor.dashboard'))->assertOk()->assertSee('data-plan-locked', false);
})->with(['cancelled', 'paused']);

test('payment and unsupported legacy states do not count as an active subscription', function (string $status) {
    $subscription = paidSellerPlan($this, 'pro');
    $subscription->update(['status' => $status]);
    expect($subscription->entitled())->toBeFalse()
        ->and(SellerSubscription::query()->entitled()->whereKey($subscription->id)->exists())->toBeFalse()
        ->and(app(SellerBenefits::class)->subscription($this->seller))->toBeNull();
    $this->actingAs($this->seller)->get(route('vendedor.dashboard'))->assertOk()->assertSee('data-plan-locked', false);
})->with(['approved', 'active', 'pending']);

test('seller plan comparison is protected and displays two approved prices without requiring a store', function () {
    $this->get(route('vendedor.suscripcion.index'))->assertRedirect(route('login'));
    $this->actingAs($this->buyer)->get(route('vendedor.suscripcion.index'))->assertForbidden();
    $this->actingAs($this->seller)->get(route('vendedor.suscripcion.index'))->assertOk()->assertSee('Impulso')->assertSee('Pro')->assertSee('$149')->assertSee('$299')->assertSee('Vendes sin suscripción')->assertSee('acepto_renovacion', false);
    $this->shop->delete();
    $this->get(route('vendedor.suscripcion.index'))->assertOk()->assertSee('Crea tu tienda para comenzar');
    $this->post(route('vendedor.suscripcion.checkout'), ['plan' => 'pro', 'acepto_renovacion' => 1])->assertRedirect(route('vendedor.tienda.create'));
    Http::assertNothingSent();
});

test('missing credentials invalid plans and missing consent cannot create subscriptions', function () {
    $this->actingAs($this->seller);
    $this->post(route('vendedor.suscripcion.checkout'), ['plan' => 'free', 'amount' => 1])->assertSessionHasErrors(['plan', 'acepto_renovacion']);
    config(['subscriptions.access_token' => null]);
    $this->get(route('vendedor.suscripcion.index'))->assertOk()->assertSee('Contratación próximamente')->assertDontSee('name="acepto_renovacion"', false);
    $this->post(route('vendedor.suscripcion.checkout'), ['plan' => 'impulso', 'acepto_renovacion' => 1])->assertSessionHasErrors('suscripcion');
    $this->assertDatabaseCount('seller_subscriptions', 0);
    Http::assertNothingSent();
});

test('checkout uses server pricing and retries reuse one mandate without activating unpaid benefits', function () {
    Http::fake(function ($request) {
        if (str_contains($request->url(), '/preapproval/search')) {
            return Http::response(['results' => []]);
        }
        if (str_contains($request->url(), '/authorized_payments/search')) {
            return Http::response(['results' => []]);
        }
        $subscription = SellerSubscription::firstOrFail();

        return Http::response(['id' => 'abc123', 'external_reference' => $subscription->id, 'collector_id' => 123, 'status' => 'pending', 'init_point' => 'https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_id=abc123', 'auto_recurring' => ['frequency' => 1, 'frequency_type' => 'months', 'transaction_amount' => 149, 'currency_id' => 'MXN']]);
    });
    $this->actingAs($this->seller);
    for ($i = 0; $i < 2; $i++) {
        $this->post(route('vendedor.suscripcion.checkout'), ['plan' => 'impulso', 'acepto_renovacion' => 1, 'amount' => 1, 'user_id' => $this->buyer->id])->assertRedirect('https://www.mercadopago.com.mx/subscriptions/checkout?preapproval_id=abc123');
    }
    $subscription = SellerSubscription::firstOrFail();
    expect($subscription->amount)->toBe('149.00')->and($subscription->user_id)->toBe($this->seller->id)->and($subscription->entitled())->toBeFalse();
    $this->assertDatabaseCount('seller_subscriptions', 1);
    expect(Http::recorded(fn ($r) => $r->method() === 'POST' && str_ends_with($r->url(), '/preapproval')))->toHaveCount(1);
    $this->post(route('vendedor.suscripcion.checkout'), ['plan' => 'pro', 'acepto_renovacion' => 1])->assertSessionHasErrors('suscripcion');
});

test('approved invoices activate exactly one month and duplicate notifications do not extend it', function () {
    $this->travelTo(now()->startOfSecond());
    $subscription = paidSellerPlan($this, 'pro');
    $subscription->update(['paid_until' => null]);
    fakeSubscriptionApi($subscription);
    $this->actingAs($this->seller)->get(route('vendedor.suscripcion.return', $subscription), ['status' => 'approved'])->assertRedirect(route('vendedor.suscripcion.index'));
    $until = $subscription->fresh()->paid_until->toDateTimeString();
    $this->post(route('vendedor.suscripcion.sync', $subscription))->assertSessionHasNoErrors();
    expect($subscription->fresh()->entitled())->toBeTrue()->and($subscription->fresh()->paid_until->toDateTimeString())->toBe($until)->and($until)->toBe(now()->addMonthNoOverflow()->toDateTimeString());
    $this->get(route('vendedor.suscripcion.index'))->assertOk()->assertSee('Pro activo')->assertSee('Resultados de tus promociones');
});

test('authorization alone declined payments refunds and collector mismatch never grant benefits', function () {
    $subscription = paidSellerPlan($this);
    $subscription->update(['paid_until' => null]);
    fakeSubscriptionApi($subscription, 'authorized', 'pending');
    app(MercadoPagoSubscriptions::class)->sync($subscription);
    expect($subscription->fresh()->entitled())->toBeFalse();
    fakeSubscriptionApi($subscription, 'authorized', 'approved', ['collector_id' => 999]);
    expect(fn () => app(MercadoPagoSubscriptions::class)->sync($subscription))->toThrow(\Illuminate\Validation\ValidationException::class);
    expect($subscription->fresh()->entitled())->toBeFalse();
    fakeSubscriptionApi($subscription);
    app(MercadoPagoSubscriptions::class)->sync($subscription);
    expect($subscription->fresh()->entitled())->toBeTrue();
    fakeSubscriptionApi($subscription, 'authorized', 'approved', ['transaction_amount_refunded' => 149]);
    app(MercadoPagoSubscriptions::class)->sync($subscription);
    expect($subscription->fresh()->entitled())->toBeFalse();
});

test('seller product promotion enforces ownership the plan limit and idempotent selection', function () {
    $subscription = paidSellerPlan($this);
    $this->actingAs($this->seller);
    $products = collect(range(1, 6))->map(fn ($n) => subscriptionProduct($this, 'Producto '.$n));
    foreach ($products->take(5) as $product) {
        $this->post(route('vendedor.suscripcion.promote', $product), ['promoted' => 1])->assertSessionHasNoErrors();
    }
    $this->post(route('vendedor.suscripcion.promote', $products->first()), ['promoted' => 1])->assertSessionHasNoErrors();
    $this->post(route('vendedor.suscripcion.promote', $products->last()), ['promoted' => 1])->assertSessionHasErrors('promocion');
    expect($subscription->products()->count())->toBe(5);
    $otherShop = $this->buyer->tiendas()->create(['nombre_tienda' => 'Otra']);
    $foreign = subscriptionProduct($this, 'Ajeno', $otherShop);
    $this->post(route('vendedor.suscripcion.promote', $foreign), ['promoted' => 1])->assertNotFound();
    $this->post(route('vendedor.suscripcion.promote', $products->first()), ['promoted' => 0])->assertSessionHasNoErrors();
    $this->post(route('vendedor.suscripcion.promote', $products->last()), ['promoted' => 1])->assertSessionHasNoErrors();
    $this->get(route('vendedor.suscripcion.index'))->assertOk()->assertSee('5 / 5 en promoción')->assertDontSee('Resultados de tus promociones');
    $subscription->update(['paid_until' => now()->subSecond()]);
    $this->post(route('vendedor.suscripcion.promote', $products->first()), ['promoted' => 1])->assertSessionHasErrors('promocion');
});

test('pro supports twenty products and never exposes another sellers subscription controls', function () {
    $subscription = paidSellerPlan($this, 'pro');
    $subscription->products()->attach(collect(range(1, 20))->map(fn ($n) => subscriptionProduct($this, 'Producto '.$n)->getKey()));
    $extra = subscriptionProduct($this, 'Extra');
    $this->actingAs($this->seller)->post(route('vendedor.suscripcion.promote', $extra), ['promoted' => 1])->assertSessionHasErrors('promocion');
    $other = User::create(['name' => 'Other seller', 'email' => 'other@example.test', 'password' => 'password', 'email_verified_at' => now(), 'activo' => true]);
    $other->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'vendedor')->value('id_tipo_usuario'));
    $this->actingAs($other)->post(route('vendedor.suscripcion.sync', $subscription))->assertNotFound();
    $this->get(route('vendedor.suscripcion.return', $subscription))->assertNotFound();
    $this->post(route('vendedor.suscripcion.cancel', $subscription), ['confirmar' => 1])->assertNotFound();
    Http::assertNothingSent();
});

test('promoted products rank first respect filters and stop when coverage expires', function () {
    $subscription = paidSellerPlan($this);
    $promoted = subscriptionProduct($this, 'Taza patrocinada');
    $promoted->update(['fecha_publicacion' => now()->subDays(10)]);
    $regular = subscriptionProduct($this, 'Novedad sin promoción');
    $subscription->products()->attach($promoted->getKey());
    $response = $this->get(route('marketplace.index'))->assertOk()->assertSee('Patrocinado')->assertSee('Tiendas recomendadas');
    expect($response->viewData('productos')->pluck('id_producto')->all())->toBe([$promoted->getKey(), $regular->getKey()]);
    $this->get(route('marketplace.index', ['q' => 'Novedad']))->assertViewHas('productos', fn ($p) => $p->pluck('id_producto')->all() === [$regular->getKey()]);
    $subscription->update(['paid_until' => now()->subSecond()]);
    $response = $this->get(route('marketplace.index'))->assertOk()->assertDontSee('Tiendas recomendadas')->assertDontSee('Patrocinado');
    expect($response->viewData('productos')->first()->getKey())->toBe($regular->getKey());
});

test('pro outranks impulso and inactive sellers receive no paid placement', function () {
    $impulso = paidSellerPlan($this);
    $first = subscriptionProduct($this, 'Impulso');
    $impulso->products()->attach($first->getKey());
    $other = User::create(['name' => 'Pro seller', 'email' => 'pro@example.test', 'password' => 'password', 'email_verified_at' => now(), 'activo' => true]);
    $other->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'vendedor')->value('id_tipo_usuario'));
    $shop = $other->tiendas()->create(['nombre_tienda' => 'Pro store']);
    $product = subscriptionProduct($this, 'Pro antiguo', $shop);
    $product->update(['fecha_publicacion' => now()->subYear()]);
    $pro = SellerSubscription::create(['user_id' => $other->id, 'plan' => 'pro', 'amount' => 299, 'status' => 'authorized', 'paid_until' => now()->addMonth()]);
    $pro->products()->attach($product->getKey());
    $response = $this->get(route('marketplace.index'))->assertOk();
    expect($response->viewData('productos')->first()->getKey())->toBe($product->getKey())->and($response->viewData('tiendasRecomendadas')->first()->getKey())->toBe($shop->getKey());
    $other->update(['activo' => false]);
    $response = $this->get(route('marketplace.index'))->assertOk();
    expect($response->viewData('productos')->first()->getKey())->toBe($first->getKey())->and($response->viewData('tiendasRecomendadas')->pluck('id_tienda')->all())->not->toContain($shop->getKey());
});

test('cancellation stops renewal keeps paid coverage and prevents a second plan during that period', function () {
    $subscription = paidSellerPlan($this);
    fakeSubscriptionApi($subscription, 'cancelled');
    $product = subscriptionProduct($this);
    $this->actingAs($this->seller);
    // Managing promoted products must not exhaust the cancellation request budget.
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('vendedor.suscripcion.promote', $product), ['promoted' => 1])->assertRedirect()->assertSessionHasNoErrors();
    }
    $this->actingAs($this->seller)->post(route('vendedor.suscripcion.cancel', $subscription))->assertSessionHasErrors('confirmar');
    $this->post(route('vendedor.suscripcion.cancel', $subscription), ['confirmar' => 1])->assertSessionHasNoErrors();
    expect($subscription->fresh()->status)->toBe('cancelled')->and($subscription->fresh()->entitled())->toBeTrue();
    Http::assertSent(fn ($r) => $r->method() === 'PUT' && $r['status'] === 'cancelled');
    $this->post(route('vendedor.suscripcion.checkout'), ['plan' => 'pro', 'acepto_renovacion' => 1])->assertSessionHasErrors('suscripcion');
    $this->get(route('vendedor.suscripcion.index'))->assertOk()->assertSee('Renovación cancelada');
});

test('webhooks reject forged signatures and verified retries reconcile without extending coverage', function () {
    $subscription = paidSellerPlan($this);
    fakeSubscriptionApi($subscription);
    $this->postJson(route('suscripciones.webhook').'?data.id=abc123', ['type' => 'subscription_preapproval', 'data' => ['id' => 'abc123']])->assertUnauthorized();
    Http::assertNothingSent();
    $signature = hash_hmac('sha256', 'id:abc123;request-id:test-request;ts:123;', 'test-secret');
    $headers = ['x-request-id' => 'test-request', 'x-signature' => 'ts=123,v1='.$signature];
    $this->postJson(route('suscripciones.webhook').'?data.id=abc123', ['type' => 'subscription_preapproval', 'data' => ['id' => 'other']], $headers)->assertUnauthorized();
    $this->postJson(route('suscripciones.webhook').'?data.id=abc123', ['type' => 'subscription_preapproval', 'data' => ['id' => 'abc123']], $headers)->assertOk();
    expect($subscription->fresh()->entitled())->toBeTrue();
});

test('promotion metrics omit seller activity deduplicate refreshes and remain scoped to the owner', function () {
    $subscription = paidSellerPlan($this, 'pro');
    $product = subscriptionProduct($this);
    $subscription->products()->attach($product->getKey());
    $this->actingAs($this->seller)->get(route('marketplace.index'))->assertOk();
    $this->get(route('productos.show', $product))->assertOk();
    $this->assertDatabaseCount('promotion_metrics', 0);
    $this->actingAs($this->buyer)->get(route('marketplace.index'))->assertOk();
    $this->get(route('marketplace.index'))->assertOk();
    $this->get(route('productos.show', $product))->assertOk();
    $this->get(route('productos.show', $product))->assertOk();
    $row = DB::table('promotion_metrics')->where('kind', 'product')->first();
    expect($row->impressions)->toBe(1)->and($row->visits)->toBe(1);
    $this->actingAs($this->seller)->get(route('vendedor.suscripcion.index'))->assertOk()->assertViewHas('metrics', fn ($m) => (int) $m['product']->visits === 1);
});
