<?php

use App\Models\TiposUsuario;
use App\Models\User;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    $buyerRole = TiposUsuario::create(['nombre_tipo' => 'comprador']);
    TiposUsuario::create(['nombre_tipo' => 'administrador']);
    $this->buyer = User::create(['name' => 'Comprador', 'email' => 'ubicacion@example.test', 'password' => 'clave-de-prueba', 'email_verified_at' => now(), 'activo' => true]);
    $this->other = User::create(['name' => 'Otra cuenta', 'email' => 'otra-ubicacion@example.test', 'password' => 'clave-de-prueba', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer->tipos_usuario()->attach($buyerRole);
    $this->other->tipos_usuario()->attach($buyerRole);
});

test('buyer location persists on the current account and stays private to that account', function () {
    $data = ['ciudad' => 'Manzanillo', 'estado' => 'Colima', 'colonia' => 'Centro', 'direccion' => 'Avenida México 10', 'codigo_postal' => '28200', 'id_usuario' => $this->other->id];
    $this->actingAs($this->buyer)->postJson(route('marketplace.ubicacion.update'), $data)->assertOk()->assertJson(['label' => 'Centro, Manzanillo']);
    unset($data['id_usuario']);
    expect($this->buyer->fresh()->ubicacion_comprador)->toBe($data)->and($this->other->fresh()->ubicacion_comprador)->toBeNull();
    $this->get(route('comprador.dashboard'))->assertOk()->assertSee('Centro, Manzanillo');
    $this->actingAs($this->other)->get(route('comprador.dashboard'))->assertOk()->assertDontSee('Centro, Manzanillo')->assertSee('Agrega tu ubicación');
    $this->actingAs($this->buyer)->postJson(route('marketplace.ubicacion.update'), ['ciudad' => 'Colima', 'estado' => 'Colima'])->assertOk()->assertJson(['label' => 'Colima']);
    expect($this->buyer->fresh()->ubicacion_comprador)->toBe(['ciudad' => 'Colima', 'estado' => 'Colima']);
});

test('guests can save a visit location without changing a user account', function () {
    $this->post(route('marketplace.ubicacion.update'), ['ciudad' => 'Manzanillo', 'estado' => 'Colima'])->assertRedirect(route('marketplace.index'))->assertSessionHas('buyer_location', ['ciudad' => 'Manzanillo', 'estado' => 'Colima']);
    $this->get(route('marketplace.index'))->assertOk()->assertSee('Manzanillo');
    expect($this->buyer->fresh()->ubicacion_comprador)->toBeNull();
    $this->actingAs($this->buyer)->get(route('comprador.dashboard'))->assertSee('Agrega tu ubicación');
});

test('map coordinates are saved privately and must be a complete valid pair', function () {
    $data = ['ciudad' => 'Manzanillo', 'estado' => 'Colima', 'latitud' => '19.0522', 'longitud' => '-104.3158'];
    $this->actingAs($this->buyer)->postJson(route('marketplace.ubicacion.update'), $data)->assertOk();
    expect($this->buyer->fresh()->ubicacion_comprador)->toBe($data);
    foreach ([['latitud' => 91, 'longitud' => -104], ['latitud' => 19], ['longitud' => -181], ['latitud' => 'NaN', 'longitud' => -104]] as $coordinates) {
        $this->postJson(route('marketplace.ubicacion.update'), ['ciudad' => 'Manzanillo', 'estado' => 'Colima'] + $coordinates)->assertUnprocessable();
        expect($this->buyer->fresh()->ubicacion_comprador)->toBe($data);
    }
    // Buyers may keep locations outside the seller service area.
    $this->postJson(route('marketplace.ubicacion.update'), ['ciudad' => 'Colima', 'estado' => 'Colima', 'latitud' => 19.243, 'longitud' => -103.724])->assertOk();
});

test('invalid locations and administrative or inactive sessions cannot update buyer locations', function () {
    $this->actingAs($this->buyer)->postJson(route('marketplace.ubicacion.update'), ['ciudad' => '', 'estado' => '', 'codigo_postal' => 'INVALID', 'direccion' => str_repeat('a', 251)])->assertUnprocessable()->assertJsonValidationErrors(['ciudad', 'estado', 'codigo_postal', 'direccion']);
    expect($this->buyer->fresh()->ubicacion_comprador)->toBeNull();
    $this->buyer->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'administrador')->value('id_tipo_usuario'));
    $this->postJson(route('marketplace.ubicacion.update'), ['ciudad' => 'Colima', 'estado' => 'Colima'])->assertForbidden();
    $this->buyer->update(['activo' => false]);
    $this->postJson(route('marketplace.ubicacion.update'), ['ciudad' => 'Colima', 'estado' => 'Colima'])->assertUnauthorized();
    expect($this->buyer->fresh()->ubicacion_comprador)->toBeNull();
});
