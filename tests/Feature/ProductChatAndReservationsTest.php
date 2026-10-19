<?php

use App\Models\Apartado;
use App\Models\Categoria;
use App\Models\MercadoPagoAccount;
use App\Models\ProductChat;
use App\Models\TiposUsuario;
use App\Models\User;
use App\Services\MercadoPagoConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    config(['mercadopago.client_id' => 'test-client', 'mercadopago.client_secret' => 'test-secret', 'mercadopago.webhook_secret' => 'webhook-secret', 'mercadopago.sandbox' => true]);
    Http::preventStrayRequests();
    $this->seller = User::create(['name' => 'Vendedor', 'email' => 'seller@example.test', 'password' => 'test-password', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer = User::create(['name' => 'Comprador', 'email' => 'buyer@example.test', 'password' => 'test-password', 'email_verified_at' => now(), 'activo' => true]);
    $this->stranger = User::create(['name' => 'Otro usuario', 'email' => 'stranger@example.test', 'password' => 'test-password', 'email_verified_at' => now(), 'activo' => true]);
    $this->sellerRole = TiposUsuario::create(['nombre_tipo' => 'vendedor']);
    $this->seller->tipos_usuario()->attach($this->sellerRole);
    $shop = $this->seller->tiendas()->create(['nombre_tienda' => 'Taller local']);
    $category = Categoria::create(['nombre_categoria' => 'Artesanía']);
    $this->product = $shop->productos()->create(['nombre_producto' => 'Taza artesanal', 'descripcion' => 'Cerámica local', 'codigo_producto' => 'CHAT-TEST', 'id_categoria' => $category->getKey(), 'precio' => 200, 'apartados_activos' => true, 'apartado_monto' => 50, 'apartado_condiciones' => 'Recoger en siete días.']);
    $this->account = MercadoPagoAccount::create(['user_id' => $this->seller->id, 'mp_user_id' => '123456', 'access_token' => 'seller-access-token', 'refresh_token' => 'seller-refresh-token', 'expires_at' => now()->addDays(10), 'live_mode' => false]);
});

function commerceReservation($test, array $overrides = []): Apartado
{
    return Apartado::create(array_replace([
        'id_producto' => $test->product->getKey(), 'buyer_id' => $test->buyer->id, 'seller_id' => $test->seller->id,
        'producto_nombre' => 'Taza artesanal', 'monto' => '50.00', 'moneda' => 'MXN', 'merchant_id' => '123456', 'condiciones' => 'Recoger en siete días.',
        'mp_preference_id' => 'PREFERENCE-TEST', 'approval_url' => 'https://sandbox.mercadopago.com.mx/checkout/v1/redirect?pref_id=PREFERENCE-TEST', 'live_mode' => false,
    ], $overrides));
}

function commercePayment(Apartado $apartado, string $status = 'approved'): array
{
    return ['id' => 987654, 'external_reference' => $apartado->id, 'collector_id' => 123456, 'currency_id' => 'MXN', 'transaction_amount' => 50, 'status' => $status, 'live_mode' => false, 'transaction_amount_refunded' => 0];
}

function commerceWebhookHeaders(string $id = '987654'): array
{
    $timestamp = (string) now()->timestamp;

    return ['x-request-id' => 'request-test', 'x-signature' => 'ts='.$timestamp.',v1='.hash_hmac('sha256', 'id:'.$id.';request-id:request-test;ts:'.$timestamp.';', 'webhook-secret')];
}

test('product chat is private reusable and separated by product', function () {
    $this->actingAs($this->buyer)->post(route('chat.start', $this->product))->assertRedirect();
    $chat = ProductChat::firstOrFail();
    $this->post(route('chat.start', $this->product))->assertRedirect(route('chat.show', $chat));
    $this->postJson(route('chat.send', $chat), ['body' => '¿Está disponible?'])->assertOk();
    $this->actingAs($this->seller)->getJson(route('chat.messages', $chat))->assertOk()->assertJsonPath('messages.0.body', '¿Está disponible?')->assertJsonPath('messages.0.mine', false);
    $this->postJson(route('chat.send', $chat), ['body' => 'Sí, podemos acordar la entrega.'])->assertOk();
    $this->actingAs($this->stranger)->get(route('chat.show', $chat))->assertNotFound();
    $this->getJson(route('chat.messages', $chat))->assertNotFound();
    $this->postJson(route('chat.send', $chat), ['body' => 'intrusión'])->assertNotFound();
    $other = $this->product->replicate(['codigo_producto']);
    $other->codigo_producto = 'OTHER-CHAT';
    $other->save();
    $this->actingAs($this->buyer)->post(route('chat.start', $other))->assertRedirect();
    expect(ProductChat::count())->toBe(2)->and(ProductChat::latest('id')->first()->conversation->messages()->count())->toBe(0);
});

test('chat requires verification and rejects self messages and general Chatify access', function () {
    $this->post(route('chat.start', $this->product))->assertRedirect(route('login'));
    $this->buyer->forceFill(['email_verified_at' => null])->save();
    $this->actingAs($this->buyer)->post(route('chat.start', $this->product))->assertRedirect(route('verification.notice'));
    $this->actingAs($this->seller)->post(route('chat.start', $this->product))->assertForbidden();
    $this->get('/api/chatify/v1/conversations')->assertNotFound();
});

test('history is paginated and unread counts belong to the correct seller inbox', function () {
    $this->actingAs($this->buyer)->post(route('chat.start', $this->product));
    $chat = ProductChat::firstOrFail();
    for ($i = 0; $i < 55; $i++) {
        $chat->conversation->messages()->create(['user_id' => $this->buyer->id, 'body' => 'Mensaje '.$i, 'created_at' => now()->subMinutes(60 - $i)]);
    }
    $this->actingAs($this->seller)->get(route('vendedor.mensajes.index'))->assertViewHas('chats', fn ($chats) => $chats->first()->unread_count === 55);
    $response = $this->getJson(route('chat.messages', $chat))->assertOk()->assertJsonCount(50, 'messages');
    $this->getJson(route('chat.messages', [$chat, 'before' => $response->json('messages.0.id')]))->assertOk()->assertJsonCount(5, 'messages');
    $this->get(route('vendedor.mensajes.index'))->assertViewHas('chats', fn ($chats) => $chats->first()->unread_count === 0);
    $this->postJson(route('chat.send', $chat), ['body' => str_repeat('x', 5001)])->assertUnprocessable();
});

test('a seller who also buys gets separate conversations and layouts for each side', function () {
    $this->actingAs($this->buyer)->post(route('chat.start', $this->product));
    $sellingChat = ProductChat::firstOrFail();
    $otherShop = $this->buyer->tiendas()->create(['nombre_tienda' => 'Otra tienda']);
    $this->buyer->tipos_usuario()->attach($this->sellerRole);
    $otherProduct = $otherShop->productos()->create(['nombre_producto' => 'Producto que compro', 'codigo_producto' => 'BUY-TEST', 'id_categoria' => $this->product->id_categoria, 'precio' => 300]);
    $this->actingAs($this->seller)->post(route('chat.start', $otherProduct));
    $buyingChat = ProductChat::latest('id')->first();
    $this->get(route('chat.index'))->assertOk()->assertViewHas('sellerArea', false)->assertViewHas('chats', fn ($chats) => $chats->pluck('id')->all() === [$buyingChat->id]);
    $this->get(route('vendedor.mensajes.index'))->assertOk()->assertViewHas('sellerArea', true)->assertViewHas('chats', fn ($chats) => $chats->pluck('id')->all() === [$sellingChat->id]);
    $this->get(route('chat.show', $buyingChat))->assertViewHas('sellerArea', false)->assertSee('Navegación del comprador');
    $this->get(route('chat.show', $sellingChat))->assertViewHas('sellerArea', true)->assertDontSee('Navegación del comprador');
});

test('buyer and seller reservations remain separate for the same account', function () {
    $sold = commerceReservation($this);
    $bought = commerceReservation($this, ['buyer_id' => $this->seller->id, 'seller_id' => $this->buyer->id, 'mp_preference_id' => 'PREFERENCE-OTHER', 'producto_nombre' => 'Otro producto']);
    $this->actingAs($this->seller)->get(route('apartados.index'))->assertOk()->assertViewHas('sellerArea', false)->assertViewHas('apartados', fn ($rows) => $rows->pluck('id')->all() === [$bought->id])->assertSee('Navegación del comprador');
    $this->get(route('vendedor.apartados.index'))->assertOk()->assertViewHas('sellerArea', true)->assertViewHas('apartados', fn ($rows) => $rows->pluck('id')->all() === [$sold->id])->assertDontSee('Navegación del comprador');
    $this->actingAs($this->buyer)->get(route('vendedor.apartados.index'))->assertForbidden();
});

test('seller navigation starts with summary then products and messages', function () {
    $html = $this->actingAs($this->seller)->get(route('vendedor.mensajes.index'))->assertOk()->getContent();
    $nav = substr($html, strpos($html, 'aria-label="Navegación de mi tienda"'));
    expect(strpos($nav, '>Resumen<'))->toBeLessThan(strpos($nav, '>Productos<'));
    expect(strpos($nav, '>Productos<'))->toBeLessThan(strpos($nav, '>Mensajes<'));
    $response = $this->get(route('chat.index'))->assertOk();
    $html = $response->getContent();
    expect(strpos($html, 'Mi tienda</a>'))->toBeLessThan(strpos($html, 'Mensajes de mi tienda</a>'));
});

test('product design keeps chat available and only shows enabled Mercado Pago apartados', function () {
    $this->actingAs($this->buyer)->get(route('productos.show', $this->product))->assertOk()->assertSee('Chatear con el vendedor')->assertSee('Apartar con Mercado Pago')->assertSee('Acerca de este producto')->assertSee('Anticipo para apartar')->assertDontSee('PayPal');
    $this->product->update(['apartados_activos' => false]);
    $this->get(route('productos.show', $this->product))->assertSee('Chatear con el vendedor')->assertDontSee('Apartar con Mercado Pago')->assertDontSee('Anticipo para apartar');
    $this->post(route('apartados.store', $this->product), ['acepto_condiciones' => 1])->assertNotFound();
    Http::assertNothingSent();
});

test('seller needs a connected account and valid terms to enable reservations', function () {
    $data = ['nombre_producto' => 'Taza artesanal', 'descripcion' => 'Cerámica', 'precio' => 200, 'id_categoria' => $this->product->id_categoria, 'apartados_activos' => 1];
    $this->actingAs($this->seller)->put(route('vendedor.productos.update', $this->product), $data)->assertSessionHasErrors(['apartado_monto', 'apartado_condiciones']);
    $data += ['apartado_monto' => 200, 'apartado_condiciones' => 'Recoger en siete días.'];
    $this->put(route('vendedor.productos.update', $this->product), $data)->assertSessionHasErrors('apartado_monto');
    $data['apartado_monto'] = 50;
    $this->put(route('vendedor.productos.update', $this->product), $data)->assertSessionHasNoErrors();
    $this->account->delete();
    $this->put(route('vendedor.productos.update', $this->product), $data)->assertSessionHasErrors('apartados_activos');
    $data['apartados_activos'] = 0;
    $this->put(route('vendedor.productos.update', $this->product), $data)->assertSessionHasNoErrors();
    expect($this->product->fresh()->apartados_activos)->toBeFalse();
});

test('checkout uses seller credentials and server price and reuses its preference on retries', function () {
    Http::fake([
        '*/checkout/preferences' => Http::response(['id' => 'PREFERENCE-TEST', 'collector_id' => 123456, 'sandbox_init_point' => 'https://sandbox.mercadopago.com.mx/checkout/v1/redirect?pref_id=PREFERENCE-TEST']),
        '*/v1/payments/search*' => Http::response(['results' => []]),
    ]);
    $data = ['acepto_condiciones' => 1, 'monto' => 1, 'merchant_id' => 'injected'];
    $this->actingAs($this->buyer)->post(route('apartados.store', $this->product), $data)->assertRedirect('https://sandbox.mercadopago.com.mx/checkout/v1/redirect?pref_id=PREFERENCE-TEST');
    $this->post(route('apartados.store', $this->product), $data)->assertRedirect('https://sandbox.mercadopago.com.mx/checkout/v1/redirect?pref_id=PREFERENCE-TEST');
    expect(Apartado::count())->toBe(1)->and(Apartado::first()->provider)->toBe('mercadopago');
    Http::assertSent(fn ($r) => str_ends_with($r->url(), '/checkout/preferences') && $r->hasHeader('Authorization', 'Bearer seller-access-token') && (float) $r['items'][0]['unit_price'] === 50.0 && $r['marketplace_fee'] === 0);
    Http::assertSentCount(2);
});

test('checkout requires consent account connection and a different buyer', function () {
    $this->actingAs($this->buyer)->post(route('apartados.store', $this->product))->assertSessionHasErrors('acepto_condiciones');
    $this->actingAs($this->seller)->post(route('apartados.store', $this->product), ['acepto_condiciones' => 1])->assertForbidden();
    config(['mercadopago.client_secret' => null]);
    $this->actingAs($this->buyer)->post(route('apartados.store', $this->product), ['acepto_condiciones' => 1])->assertStatus(503);
    $this->get(route('productos.show', $this->product))->assertSee('Chatear con el vendedor')->assertDontSee('Apartar con Mercado Pago');
    Http::assertNothingSent();
});

test('return status is confirmed by the API and completed reservations cannot reopen checkout', function () {
    $apartado = commerceReservation($this);
    Http::fake(['*/v1/payments/987654' => Http::response(commercePayment($apartado))]);
    $this->actingAs($this->buyer)->get(route('apartados.return', [$apartado, 'payment_id' => '987654', 'status' => 'rejected']))->assertRedirect(route('apartados.index'));
    expect($apartado->fresh()->estado)->toBe('pagado')->and($apartado->fresh()->mp_payment_id)->toBe('987654');
    $this->post(route('apartados.resume', $apartado))->assertRedirect(route('apartados.index'));
    $this->actingAs($this->stranger)->get(route('apartados.return', [$apartado, 'payment_id' => '987654']))->assertNotFound();
    $this->post(route('apartados.resume', $apartado))->assertNotFound();
});

test('payment receiver currency reference amount and mode must match', function (string $field, mixed $value) {
    $apartado = commerceReservation($this);
    $payment = commercePayment($apartado);
    $payment[$field] = $value;
    Http::fake(['*/v1/payments/987654' => Http::response($payment)]);
    $this->actingAs($this->buyer)->get(route('apartados.return', [$apartado, 'payment_id' => '987654']))->assertSessionHasErrors('mercadopago');
    expect($apartado->fresh()->estado)->toBe('pendiente');
})->with([['collector_id', 999], ['currency_id', 'USD'], ['external_reference', 'another-reservation'], ['transaction_amount', 1], ['transaction_amount', 50.001], ['live_mode', true]]);

test('query approval without an approved payment cannot mark a reservation paid', function () {
    $apartado = commerceReservation($this);
    Http::fake(['*/v1/payments/search*' => Http::response(['results' => []])]);
    $this->actingAs($this->buyer)->get(route('apartados.return', [$apartado, 'status' => 'approved']))->assertRedirect(route('apartados.index'));
    expect($apartado->fresh()->estado)->toBe('pendiente');
});

test('signed webhook confirms payments and invalid signatures never call the provider', function () {
    $apartado = commerceReservation($this);
    Http::fake(['*/v1/payments/987654' => Http::response(commercePayment($apartado))]);
    $url = route('mercadopago.webhook', ['account' => $this->account->id, 'data.id' => '987654']);
    $this->postJson($url, ['type' => 'payment', 'data' => ['id' => '987654']], ['x-signature' => 'invalid'])->assertUnauthorized();
    Http::assertNothingSent();
    $this->postJson($url, ['type' => 'payment', 'data' => ['id' => '987654']], commerceWebhookHeaders())->assertOk();
    $this->postJson($url, ['type' => 'payment', 'data' => ['id' => '987654']], commerceWebhookHeaders())->assertOk();
    expect($apartado->fresh()->estado)->toBe('pagado');
});

test('pending rejected refunded and reversed states follow authoritative provider responses', function () {
    $apartado = commerceReservation($this);
    Http::fake(['*/v1/payments/987654' => Http::sequence()->push(commercePayment($apartado, 'pending'))->push(commercePayment($apartado, 'rejected'))->push(commercePayment($apartado))->push(commercePayment($apartado, 'refunded'))->push(commercePayment($apartado))]);
    $url = route('mercadopago.webhook', ['account' => $this->account->id, 'data.id' => '987654']);
    foreach (['procesando', 'rechazado', 'pagado', 'reembolsado', 'reembolsado'] as $expected) {
        $this->postJson($url, ['type' => 'payment', 'data' => ['id' => '987654']], commerceWebhookHeaders())->assertOk();
        expect($apartado->fresh()->estado)->toBe($expected);
    }
});

test('OAuth connection uses PKCE state and encrypted tokens', function () {
    $this->account->delete();
    $response = $this->actingAs($this->seller)->post(route('vendedor.mercadopago.connect'))->assertRedirect();
    parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
    $attempt = session('mp_oauth');
    expect($query['state'])->toBe($attempt['state'])->and($query['code_challenge_method'])->toBe('S256')->and($query['code_challenge'])->toBe(rtrim(strtr(base64_encode(hash('sha256', $attempt['verifier'], true)), '+/', '-_'), '='));
    Http::fake(['*/oauth/token' => Http::response(['user_id' => 123456, 'access_token' => 'new-access', 'refresh_token' => 'new-refresh', 'expires_in' => 3600, 'live_mode' => false])]);
    $this->get(route('vendedor.mercadopago.callback', ['state' => $attempt['state'], 'code' => 'code-test']))->assertRedirect(route('vendedor.apartados.index'));
    $account = MercadoPagoAccount::firstOrFail();
    expect($account->access_token)->toBe('new-access')->and(DB::table('mercado_pago_accounts')->value('access_token'))->not->toBe('new-access')->and($account->toArray())->not->toHaveKey('access_token');
    Http::assertSent(fn ($r) => $r['code_verifier'] === $attempt['verifier'] && $r['test_token'] === true);
    $this->get(route('vendedor.mercadopago.callback', ['state' => $attempt['state'], 'code' => 'code-test']))->assertForbidden();
});

test('OAuth rejects invalid or expired state and refreshes expired credentials securely', function () {
    $this->actingAs($this->seller)->withSession(['mp_oauth' => ['state' => 'correct', 'verifier' => 'secret-verifier', 'user_id' => $this->seller->id, 'expires_at' => now()->subMinute()->timestamp]])->get(route('vendedor.mercadopago.callback', ['state' => 'incorrect', 'code' => 'code-test']))->assertForbidden();
    Http::assertNothingSent();
    $this->account->update(['expires_at' => now()->subMinute()]);
    Http::fake(['*/oauth/token' => Http::response(['user_id' => 123456, 'access_token' => 'renewed-access', 'refresh_token' => 'renewed-refresh', 'expires_in' => 3600, 'live_mode' => false])]);
    expect(app(MercadoPagoConnection::class)->token($this->account))->toBe('renewed-access');
    Http::assertSent(fn ($r) => $r['grant_type'] === 'refresh_token' && $r['refresh_token'] === 'seller-refresh-token');
});

test('previous accepted terms are preserved and network failures leave a retryable reservation', function () {
    $apartado = commerceReservation($this);
    $this->product->update(['apartado_monto' => 75]);
    $this->actingAs($this->buyer)->post(route('apartados.store', $this->product), ['acepto_condiciones' => 1])->assertRedirect(route('apartados.index'));
    expect($apartado->fresh()->monto)->toBe('50.00');
    Http::assertNothingSent();
    $apartado->delete();
    Http::fake(['*/checkout/preferences' => Http::response([], 503)]);
    $this->post(route('apartados.store', $this->product), ['acepto_condiciones' => 1])->assertSessionHasErrors('mercadopago');
    expect(Apartado::first()->estado)->toBe('pendiente')->and(Apartado::first()->mp_preference_id)->toBeNull();
});

test('staff cannot access either marketplace commerce area', function () {
    $role = TiposUsuario::create(['nombre_tipo' => 'moderador']);
    $this->stranger->tipos_usuario()->attach($role);
    $this->actingAs($this->stranger)->get(route('chat.index'))->assertForbidden();
    $this->post(route('apartados.store', $this->product), ['acepto_condiciones' => 1])->assertForbidden();
    Http::assertNothingSent();
});

test('refreshed credentials persist even if creating the checkout fails', function () {
    $this->account->update(['expires_at' => now()->subMinute()]);
    Http::fake([
        '*/oauth/token' => Http::response(['user_id' => 123456, 'access_token' => 'renewed-access', 'refresh_token' => 'renewed-refresh', 'expires_in' => 3600, 'live_mode' => false]),
        '*/checkout/preferences' => Http::response([], 503),
    ]);
    $this->actingAs($this->buyer)->post(route('apartados.store', $this->product), ['acepto_condiciones' => 1])->assertSessionHasErrors('mercadopago');
    expect($this->account->fresh()->refresh_token)->toBe('renewed-refresh');
    Http::assertSent(fn ($r) => str_ends_with($r->url(), '/checkout/preferences') && $r->hasHeader('Authorization', 'Bearer renewed-access'));
});
