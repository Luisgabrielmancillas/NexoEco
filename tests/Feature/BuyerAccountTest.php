<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\TiposUsuario;
use App\Models\User;
use App\Notifications\BuyerActivityNotification;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    $role = TiposUsuario::create(['nombre_tipo' => 'comprador']);
    $this->buyer = User::create(['name' => 'Comprador de prueba', 'email' => 'comprador@example.test', 'password' => 'clave-de-prueba', 'email_verified_at' => now(), 'activo' => true]);
    $this->other = User::create(['name' => 'Otra cuenta', 'email' => 'otra@example.test', 'password' => 'clave-de-prueba', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer->tipos_usuario()->attach($role);
    $this->other->tipos_usuario()->attach($role);
    $this->store = Tienda::create(['id_vendedor' => $this->other->id, 'nombre_tienda' => 'Taller del catálogo', 'descripcion_tienda' => 'Productos del vendedor', 'fecha_creacion' => now()]);
    $this->category = Categoria::create(['nombre_categoria' => 'Artesanía']);
    $this->product = Producto::create(['id_tienda' => $this->store->getKey(), 'id_categoria' => $this->category->getKey(), 'codigo_producto' => 'CAT-001', 'nombre_producto' => 'Jarrón del catálogo', 'precio' => 275.50, 'fecha_publicacion' => now()]);
});

test('department shortcuts filter real products and stores consistently for guests and buyers', function () {
    $pets = Categoria::create(['nombre_categoria' => 'Alimentos para mascotas']);
    $food = Categoria::create(['nombre_categoria' => 'Comida preparada']);
    $petStore = Tienda::create(['id_vendedor' => $this->other->id, 'nombre_tienda' => 'Mascotas del puerto']);
    $pet = $petStore->productos()->create(['id_categoria' => $pets->getKey(), 'codigo_producto' => 'PET-1', 'nombre_producto' => 'Alimento para perro', 'precio' => 250]);
    $this->store->productos()->create(['id_categoria' => $food->getKey(), 'codigo_producto' => 'FOOD-1', 'nombre_producto' => 'Comida del día', 'precio' => 90]);
    foreach (['marketplace.index', 'comprador.dashboard'] as $route) {
        if ($route === 'comprador.dashboard') {
            $this->actingAs($this->buyer);
        }
        $response = $this->get(route($route, ['seccion' => 'mascotas']))->assertOk();
        expect($response->viewData('productos')->modelKeys())->toBe([$pet->getKey()])
            ->and($response->viewData('tiendas')->modelKeys())->toBe([$petStore->getKey()]);
        $this->get(route($route, ['seccion' => 'mascotas', 'q' => 'perro']))->assertViewHas('productos', fn ($items) => $items->total() === 1);
        $this->get(route($route, ['seccion' => 'electronicos']))->assertViewHas('productos', fn ($items) => $items->isEmpty())->assertViewHas('tiendas', fn ($items) => $items->isEmpty());
        $this->getJson(route($route, ['seccion' => 'inventada']))->assertUnprocessable()->assertJsonValidationErrors('seccion');
    }
});

test('buyer home and public marketplace share the real catalog and filters', function () {
    $public = $this->get(route('marketplace.index'))->assertOk()->assertSee('Jarrón del catálogo')->assertSee('Taller del catálogo');
    $home = $this->actingAs($this->buyer)->get(route('comprador.dashboard'))->assertOk()->assertViewIs('marketplace.index')
        ->assertSee('Favoritos')->assertSee('Mis opiniones')->assertSee('Notificaciones')->assertSee('Preguntas frecuentes');
    expect($home->viewData('productos')->modelKeys())->toBe($public->viewData('productos')->modelKeys())
        ->and($home->viewData('tiendas')->modelKeys())->toBe($public->viewData('tiendas')->modelKeys());
    $this->get(route('comprador.dashboard', ['q' => 'Inexistente']))->assertOk()->assertViewHas('productos', fn ($items) => $items->isEmpty());
    $this->get(route('comprador.dashboard', ['categoria' => $this->category->getKey()]))->assertOk()->assertViewHas('productos', fn ($items) => $items->total() === 1);
    $this->get(route('profile.edit'))->assertOk()->assertViewIs('profile.edit')->assertSee('name="email"', false);
});

test('product and store favorites persist idempotently and belong to the current user', function (string $type) {
    $item = $type === 'productos' ? $this->product : $this->store;
    $url = route('comprador.favoritos.store', [$type, $item->getKey()]);
    $this->actingAs($this->buyer)->postJson($url)->assertOk()->assertJson(['saved' => true]);
    $this->postJson($url)->assertOk()->assertJson(['saved' => true]);
    $table = $type === 'productos' ? 'productos_favoritos' : 'tiendas_favoritas';
    $this->assertDatabaseCount($table, 1);
    $this->get(route('comprador.favoritos', $type))->assertOk()->assertSee('aria-pressed="true"', false)->assertViewHas('items', fn ($items) => $items->total() === 1);
    $this->actingAs($this->other)->get(route('comprador.favoritos', $type))->assertOk()->assertViewHas('items', fn ($items) => $items->isEmpty());
    $this->deleteJson($url)->assertOk();
    $this->assertDatabaseCount($table, 1);
    $this->actingAs($this->buyer)->deleteJson($url)->assertOk()->assertJson(['saved' => false]);
    $this->assertDatabaseCount($table, 0);
    $this->get(route('comprador.favoritos', $type))->assertOk()->assertViewHas('items', fn ($items) => $items->isEmpty());
})->with(['productos', 'tiendas']);

test('account sections require authentication and a verified email', function () {
    $routes = [route('comprador.dashboard'), route('comprador.favoritos', 'productos'), route('comprador.opiniones'), route('comprador.notificaciones'), route('comprador.soporte', 'contacto')];
    foreach ($routes as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }
    $this->postJson(route('comprador.favoritos.store', ['productos', $this->product->getKey()]))->assertUnauthorized();
    $this->buyer->update(['email_verified_at' => null]);
    foreach ($routes as $url) {
        $this->actingAs($this->buyer)->get($url)->assertRedirect(route('verification.notice'));
    }
    $this->postJson(route('comprador.favoritos.store', ['productos', $this->product->getKey()]))->assertForbidden();
    $this->assertDatabaseCount('productos_favoritos', 0);
});

test('reviews can be published updated displayed and deleted only by their author', function (string $type) {
    $item = $type === 'productos' ? $this->product : $this->store;
    $url = route('comprador.opiniones.store', [$type, $item->getKey()]);
    $detail = route($type === 'productos' ? 'productos.show' : 'tiendas.show', $item);
    $this->actingAs($this->buyer)->post($url, ['calificacion' => 5, 'comentario' => 'Mi experiencia con el catálogo'])->assertRedirect($detail.'#opiniones');
    $this->post($url, ['calificacion' => 4, 'comentario' => 'Mi experiencia actualizada'])->assertRedirect();
    $this->assertDatabaseCount('opiniones', 1);
    $review = $this->buyer->opiniones()->firstOrFail();
    expect($review->calificacion)->toBe(4);
    $this->get($detail)->assertOk()->assertSee('Mi experiencia actualizada')->assertSee('4.0 de 5')->assertDontSee('pendiente de revisión')->assertSee('Editar mi opinión');
    $review->forceFill(['estado_moderacion' => 'aprobada'])->save();
    $this->get($detail)->assertOk()->assertSee('4.0 de 5');
    $this->get(route('comprador.opiniones', ['tipo' => $type]))->assertOk()->assertSee('Mi experiencia actualizada');
    $this->actingAs($this->other)->get(route('comprador.opiniones'))->assertOk()->assertDontSee('Mi experiencia actualizada');
    $this->delete(route('comprador.opiniones.destroy', $review->id))->assertNotFound();
    $this->assertDatabaseCount('opiniones', 1);
    $this->actingAs($this->buyer)->delete(route('comprador.opiniones.destroy', $review->id))->assertRedirect();
    expect(\App\Models\Opinion::count())->toBe(0);
})->with(['productos', 'tiendas']);

test('reviews validate ratings and text and cannot target nonexistent products', function () {
    $this->actingAs($this->buyer)->post(route('comprador.opiniones.store', ['productos', $this->product->getKey()]), ['calificacion' => 6, 'comentario' => 'abc'])->assertSessionHasErrors(['calificacion', 'comentario']);
    $this->post(route('comprador.opiniones.store', ['productos', 99999]), ['calificacion' => 5, 'comentario' => 'Reseña válida'])->assertNotFound();
    $this->postJson(route('comprador.favoritos.store', ['tiendas', 99999]))->assertNotFound();
    expect(\App\Models\Opinion::count())->toBe(0);
});

test('support persists real tickets and notifies only their owner', function () {
    $this->actingAs($this->buyer)->post(route('comprador.soporte.store'), ['asunto' => 'Consulta sobre tienda', 'mensaje' => 'Necesito ayuda con la información de esta tienda.'])->assertRedirect(route('comprador.soporte', 'contacto'));
    $this->assertDatabaseHas('solicitudes_soporte', ['id_usuario' => $this->buyer->id, 'asunto' => 'Consulta sobre tienda', 'estado' => 'abierta']);
    $this->get(route('comprador.soporte', 'contacto'))->assertOk()->assertSee('Consulta sobre tienda')->assertSee('Recibida');
    $this->get(route('comprador.notificaciones'))->assertOk()->assertSee('Recibimos tu solicitud de soporte')->assertSee('Marcar todas como leídas');
    expect($this->buyer->unreadNotifications()->count())->toBe(1);
    $this->actingAs($this->other)->get(route('comprador.soporte', 'contacto'))->assertOk()->assertDontSee('Consulta sobre tienda');
    $this->get(route('comprador.notificaciones'))->assertOk()->assertDontSee('Recibimos tu solicitud de soporte');
    $this->post(route('comprador.soporte.store'), ['asunto' => '', 'mensaje' => 'corto'])->assertSessionHasErrors(['asunto', 'mensaje']);
    $this->assertDatabaseCount('solicitudes_soporte', 1);
    foreach (['ayuda', 'preguntas-frecuentes'] as $section) {
        $this->get(route('comprador.soporte', $section))->assertOk();
    }
});

test('notification read actions cannot alter another account and clear the unread badge', function () {
    $this->buyer->notify(new BuyerActivityNotification('Aviso de mi cuenta', 'Mensaje de mi cuenta', route('comprador.dashboard')));
    $this->other->notify(new BuyerActivityNotification('Aviso privado', 'Mensaje privado', route('comprador.dashboard')));
    $notification = $this->buyer->notifications()->firstOrFail();
    $this->actingAs($this->other)->post(route('comprador.notificaciones.read', $notification->id))->assertNotFound();
    expect($notification->fresh()->read_at)->toBeNull();
    $this->actingAs($this->buyer)->get(route('comprador.notificaciones'))->assertOk()->assertViewHas('notificacionesSinLeer', 1);
    $this->post(route('comprador.notificaciones.read', $notification->id))->assertRedirect();
    $this->get(route('comprador.notificaciones'))->assertOk()->assertViewHas('notificacionesSinLeer', 0);
    $this->buyer->notify(new BuyerActivityNotification('Segundo aviso', 'Otro mensaje', route('comprador.dashboard')));
    $this->post(route('comprador.notificaciones.readAll'))->assertRedirect();
    expect($this->buyer->unreadNotifications()->count())->toBe(0)->and($this->other->unreadNotifications()->count())->toBe(1);
});

test('guests can browse the catalog but must log in to open product and store details', function (string $type) {
    $item = $type === 'productos' ? $this->product : $this->store;
    $detail = route($type === 'productos' ? 'productos.show' : 'tiendas.show', $item);
    $this->get(route('marketplace.index'))->assertOk()->assertSee($detail, false);
    $this->get($detail)->assertRedirect(route('login'))->assertSessionHas('url.intended', $detail);
    $this->post(route('login'), ['email' => $this->buyer->email, 'password' => 'clave-de-prueba'])->assertRedirect($detail);
    $this->get($detail)->assertOk();
})->with(['productos', 'tiendas']);

test('notification count is private and the bell appears in the header rather than the nav', function () {
    $this->getJson(route('comprador.notificaciones.count'))->assertUnauthorized();
    $this->buyer->notify(new BuyerActivityNotification('Aviso propio', 'Mi mensaje', route('comprador.dashboard')));
    $this->other->notify(new BuyerActivityNotification('Otro aviso', 'Otro mensaje', route('comprador.dashboard')));
    $this->other->notify(new BuyerActivityNotification('Otro aviso más', 'Otro mensaje', route('comprador.dashboard')));
    $this->actingAs($this->buyer)->getJson(route('comprador.notificaciones.count'))->assertOk()->assertExactJson(['unread' => 1]);
    $response = $this->get(route('comprador.dashboard'))->assertOk()->assertSee('class="notification-count"', false)->assertSee('Notificaciones: 1 sin leer');
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
    $xpath = new DOMXPath($document);
    expect($xpath->query('//header//a[@data-notification-bell]')->length)->toBe(1)
        ->and($xpath->query('//nav[@data-buyer-nav]//a[contains(@href,"notificaciones")]')->length)->toBe(0)
        ->and($xpath->query('//nav[@data-buyer-nav]//summary')->length)->toBe(2);
    $this->actingAs($this->other)->getJson(route('comprador.notificaciones.count'))->assertExactJson(['unread' => 2]);
});

test('account form keeps the display name synchronized without granting roles', function () {
    $this->buyer->update(['nombre_completo' => 'Nombre completo anterior']);
    $this->actingAs($this->buyer)->get(route('profile.edit'))->assertOk()->assertSee('Nombre completo anterior')->assertSee('Guardar mis datos')->assertSee('Actualizar contraseña')->assertDontSee('Profile Information');
    $this->patch(route('profile.update'), ['name' => '  Nombre actualizado  ', 'email' => $this->buyer->email, 'activo' => false, 'tipo_registro' => 'vendedor'])->assertRedirect(route('profile.edit'));
    $this->buyer->refresh();
    expect($this->buyer->name)->toBe('Nombre actualizado')->and($this->buyer->nombre_completo)->toBe('Nombre actualizado')
        ->and($this->buyer->hasVerifiedEmail())->toBeTrue()->and($this->buyer->activo)->toBeTrue()->and($this->buyer->tieneTipo('vendedor'))->toBeFalse();
    $this->get(route('profile.edit'))->assertOk()->assertSee('Tus datos se actualizaron correctamente.')->assertDontSee('profile-updated');
});

test('changing email requires verification and rejects an email belonging to another user', function () {
    $this->actingAs($this->buyer)->patch(route('profile.update'), ['name' => 'Mi nombre', 'email' => $this->other->email])->assertSessionHasErrors('email');
    expect($this->buyer->fresh()->email)->toBe('comprador@example.test');
    $this->patch(route('profile.update'), ['name' => 'Mi nombre', 'email' => '  NUEVA@example.test  '])->assertRedirect(route('profile.edit'));
    $this->buyer->refresh();
    expect($this->buyer->email)->toBe('nueva@example.test')->and($this->buyer->hasVerifiedEmail())->toBeFalse();
    $this->get(route('profile.edit'))->assertOk()->assertSee('Correo pendiente de verificar')->assertSee('Introducir código')->assertSee('Reenviar código');
});

test('account password change requires the current password and matching confirmation', function () {
    $this->actingAs($this->buyer)->from(route('profile.edit'))->put(route('password.update'), ['current_password' => 'incorrecta', 'password' => 'nueva-clave-123', 'password_confirmation' => 'otra-clave-123'])
        ->assertSessionHasErrorsIn('updatePassword', ['current_password', 'password'])->assertSessionMissing('_old_input.password')->assertSessionMissing('_old_input.current_password');
    $this->get(route('profile.edit'))->assertOk()->assertSee('La contraseña actual no es correcta.')->assertSee('Las contraseñas no coinciden.');
    $this->put(route('password.update'), ['current_password' => 'clave-de-prueba', 'password' => 'nueva-clave-123', 'password_confirmation' => 'nueva-clave-123'])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));
    expect(\Illuminate\Support\Facades\Hash::check('nueva-clave-123', $this->buyer->fresh()->password))->toBeTrue();
    $this->get(route('profile.edit'))->assertSee('Tu contraseña se actualizó correctamente.');
});
