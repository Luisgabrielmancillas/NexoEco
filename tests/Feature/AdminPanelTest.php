<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\TiposUsuario;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Storage::fake('local');
    // Deliberately use a different role order than the old hard-coded administrator ID.
    foreach (['vendedor', 'administrador', 'comprador', 'moderador'] as $role) {
        TiposUsuario::create(['nombre_tipo' => $role]);
    }
    $this->admin = User::create(['name' => 'Administradora de prueba', 'email' => 'admin@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
    $this->admin->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'administrador')->value('id_tipo_usuario'));
    $this->buyer = User::create(['name' => 'Comprador de prueba', 'email' => 'buyer@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'comprador')->value('id_tipo_usuario'));
    $this->ticket = $this->buyer->solicitudesSoporte()->create(['asunto' => 'Ayuda con mi tienda', 'mensaje' => 'Necesito ayuda con una consulta de la tienda.']);
    $this->application = $this->buyer->solicitudVendedor()->create(['estado' => 'en_revision', 'rfc' => 'LOPM900101AB1', 'fecha_solicitud' => now(), 'fecha_envio' => now()]);
});

test('administration routes require an active verified administrator', function (string $role) {
    $this->buyer->tipos_usuario()->sync([TiposUsuario::where('nombre_tipo', $role)->value('id_tipo_usuario')]);
    $routes = [route('administrador.dashboard'), route('admin.users.index'), route('admin.users.create'), route('admin.users.show', $this->admin), route('admin.users.edit', $this->admin), route('admin.consultas.index'), route('admin.consultas.show', $this->ticket), route('admin.solicitudes.index'), route('admin.solicitudes.show', $this->application), route('admin.catalogo', 'productos'), route('admin.opiniones')];
    foreach ($routes as $url) {
        $this->actingAs($this->buyer)->get($url)->assertForbidden();
    }
    $this->post(route('admin.users.store'), ['name' => 'Intruso', 'email' => 'intruso@example.test', 'password' => 'clave-segura-123', 'rol' => 'administrador'])->assertForbidden();
    $this->put(route('admin.users.update', $this->admin), ['name' => 'Intruso', 'email' => 'intruso@example.test'])->assertForbidden();
    $this->delete(route('admin.users.destroy', $this->admin))->assertForbidden();
    $this->post(route('admin.consultas.reply', $this->ticket), ['mensaje' => 'Respuesta no autorizada', 'estado' => 'cerrada'])->assertForbidden();
    $this->post(route('admin.solicitudes.review', $this->application), ['estado' => 'aprobada'])->assertForbidden();
    $this->get(route('tipos-usuario.index'))->assertForbidden();
    $this->assertDatabaseCount('users', 2);
    $this->assertDatabaseCount('respuestas_soporte', 0);
})->with(['comprador', 'vendedor', 'moderador']);

test('dashboard counts and graphs reflect all users and the actual catalog', function () {
    $old = User::create(['name' => 'Usuario anterior', 'email' => 'old@example.test', 'password' => 'clave-segura-123', 'activo' => false, 'fecha_registro' => now()->startOfMonth()->subMonths(2)]);
    $old->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'moderador')->value('id_tipo_usuario'));
    $store = Tienda::create(['id_vendedor' => $this->buyer->id, 'nombre_tienda' => 'Tienda real de prueba']);
    $category = Categoria::create(['nombre_categoria' => 'Cerámica']);
    Producto::create(['id_tienda' => $store->getKey(), 'id_categoria' => $category->getKey(), 'codigo_producto' => 'REAL-001', 'nombre_producto' => 'Producto real de prueba', 'precio' => 100]);
    $response = $this->actingAs($this->admin)->get(route('administrador.dashboard'))->assertOk()->assertSee('Registro de usuarios')->assertDontSee('Ayuda con mi tienda')->assertDontSee('Consultas pendientes')->assertSee('Consultas por atender');
    expect($response->viewData('stats'))->toMatchArray(['usuarios' => 3, 'activos' => 2, 'inactivos' => 1, 'productos' => 1, 'tiendas' => 1, 'consultas' => 1, 'solicitudes' => 1])
        ->and($response->viewData('months')->pluck('total')->all())->toBe([0, 0, 0, 1, 0, 2])
        ->and($response->viewData('roles')['moderador']['total'])->toBe(1);
    $this->get(route('admin.catalogo', 'productos'))->assertOk()->assertSee('Producto real de prueba');
    $this->get(route('admin.catalogo', 'tiendas'))->assertOk()->assertSee('Tienda real de prueba');
    $this->get(route('admin.opiniones'))->assertOk()->assertSee('Aún no hay opiniones');
});

test('guests and unverified administrators cannot execute administrative actions', function () {
    $this->get(route('administrador.dashboard'))->assertRedirect(route('login'));
    $data = ['name' => 'Cuenta bloqueada', 'email' => 'blocked@example.test', 'password' => 'clave-segura-123', 'rol' => 'administrador'];
    $this->post(route('admin.users.store'), $data)->assertRedirect(route('login'));
    $this->admin->update(['email_verified_at' => null]);
    $this->actingAs($this->admin)->get(route('administrador.dashboard'))->assertRedirect(route('verification.notice'));
    $this->post(route('admin.users.store'), $data)->assertRedirect(route('verification.notice'));
    $this->assertDatabaseCount('users', 2);
});

test('quick creation assigns the selected role with a verified email and no verification message', function (string $role) {
    Notification::fake();
    $this->actingAs($this->admin)->post(route('admin.users.store'), ['name' => '  Nueva cuenta  ', 'email' => ' NUEVA@example.test ', 'password' => 'clave-segura-123', 'rol' => $role])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index', ['rol' => $role]));
    $user = User::where('email', 'nueva@example.test')->firstOrFail();
    expect($user->name)->toBe('Nueva cuenta')->and($user->hasVerifiedEmail())->toBeTrue()->and($user->activo)->toBeTrue()
        ->and($user->tieneTipo($role))->toBeTrue()->and($user->tipos_usuario()->count())->toBe(1);
    Notification::assertNothingSent();
    $this->get(route('admin.users.index', ['rol' => $role]))->assertOk()->assertViewHas('users', fn ($items) => $items->contains('id', $user->id));
    $this->post(route('logout'));
    $this->post(route('login'), ['email' => $user->email, 'password' => 'clave-segura-123'])->assertRedirect(route($role === 'administrador' ? 'administrador.dashboard' : ($role === 'moderador' ? 'moderador.dashboard' : 'comprador.dashboard')));
})->with(['comprador', 'moderador', 'administrador']);

test('quick creation rejects direct sellers invalid passwords and duplicate emails', function () {
    $this->actingAs($this->admin)->post(route('admin.users.store'), ['name' => 'Usuario nuevo', 'email' => 'seller@example.test', 'password' => 'clave-segura-123', 'rol' => 'vendedor'])->assertSessionHasErrors('rol');
    $this->post(route('admin.users.store'), ['name' => 'Usuario nuevo', 'email' => $this->buyer->email, 'password' => 'corta', 'rol' => 'comprador'])->assertSessionHasErrors(['email', 'password'])->assertSessionMissing('_old_input.password');
    $this->assertDatabaseCount('users', 2);
    $this->get(route('admin.users.create'))->assertOk()->assertDontSee('value="vendedor"', false);
});

test('user filters and editing use real roles and preserve seller approval requirements', function () {
    $this->actingAs($this->admin)->get(route('admin.users.index', ['rol' => 'comprador', 'q' => 'buyer@example.test']))->assertOk()->assertViewHas('users', fn ($users) => $users->total() === 1 && $users->first()->id === $this->buyer->id);
    $this->get(route('admin.users.index', ['rol' => 'vendedor']))->assertOk()->assertViewHas('users', fn ($users) => $users->total() === 0);
    $this->put(route('admin.users.update', $this->buyer), ['name' => 'Nombre actualizado', 'email' => 'nuevo@example.test', 'rol' => 'vendedor'])->assertRedirect(route('admin.users.edit', $this->buyer));
    expect($this->buyer->fresh()->name)->toBe('Nombre actualizado')->and($this->buyer->fresh()->email)->toBe('nuevo@example.test')->and($this->buyer->tieneTipo('vendedor'))->toBeFalse();
    $this->get(route('admin.users.edit', $this->buyer))->assertOk()->assertSee('Abrir documentación');
});

test('deactivated users cannot log in or continue existing sessions and can be reactivated', function () {
    $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin))->assertSessionHas('error');
    expect($this->admin->fresh()->activo)->toBeTrue();
    $this->delete(route('admin.users.destroy', $this->buyer))->assertRedirect();
    expect($this->buyer->fresh()->activo)->toBeFalse();
    $this->actingAs($this->buyer->fresh())->get(route('comprador.dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
    $this->post(route('login'), ['email' => $this->buyer->email, 'password' => 'clave-segura-123'])->assertSessionHasErrors('email');
    $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->buyer))->assertRedirect();
    expect($this->buyer->fresh()->activo)->toBeTrue();
});

test('admin support replies reach the buyer as a private conversation and notification', function () {
    $this->actingAs($this->admin)->get(route('admin.consultas.index', ['q' => 'buyer@example.test']))->assertOk()->assertSee('Ayuda con mi tienda');
    $this->get(route('admin.consultas.show', $this->ticket))->assertOk()->assertSee('Responder al usuario');
    $this->post(route('admin.consultas.reply', $this->ticket), ['mensaje' => 'Respuesta real del equipo de soporte.', 'estado' => 'cerrada'])->assertRedirect(route('admin.consultas.show', $this->ticket));
    expect($this->ticket->fresh()->estado)->toBe('cerrada')->and($this->buyer->unreadNotifications()->count())->toBe(1);
    $notification = $this->buyer->notifications()->firstOrFail();
    expect($notification->data['url'])->toBe(route('comprador.soporte.show', $this->ticket));
    $this->actingAs($this->buyer)->get(route('comprador.soporte.show', $this->ticket))->assertOk()->assertSee('Respuesta real del equipo de soporte.')->assertSee('Resuelta');
    $this->get(route('comprador.notificaciones'))->assertOk()->assertSee('Respuesta a tu consulta #'.$this->ticket->id);
    $this->post(route('comprador.soporte.reply', $this->ticket), ['mensaje' => 'Necesito otra aclaración, por favor.', 'es_administrador' => true, 'id_usuario' => $this->admin->id])->assertRedirect();
    expect($this->ticket->fresh()->estado)->toBe('abierta')->and($this->ticket->respuestas()->reorder('id', 'desc')->first()->es_administrador)->toBeFalse();
    $this->actingAs($this->admin)->get(route('admin.consultas.show', $this->ticket))->assertOk()->assertSee('Necesito otra aclaración, por favor.');
});

test('support conversations prevent access by other buyers and reject empty replies', function () {
    $other = User::create(['name' => 'Otro comprador', 'email' => 'otro@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
    $this->actingAs($other)->get(route('comprador.soporte.show', $this->ticket))->assertNotFound();
    $this->post(route('comprador.soporte.reply', $this->ticket), ['mensaje' => 'Respuesta ajena'])->assertNotFound();
    $this->actingAs($this->admin)->post(route('admin.consultas.reply', $this->ticket), ['mensaje' => '', 'estado' => 'cerrada'])->assertSessionHasErrors('mensaje');
    $this->assertDatabaseCount('respuestas_soporte', 0);
    expect($this->ticket->fresh()->estado)->toBe('abierta')->and($this->buyer->notifications()->count())->toBe(0);
});

test('seller approval requires every document and then enables the seller role once', function () {
    $this->actingAs($this->admin)->post(route('admin.solicitudes.review', $this->application), ['estado' => 'aprobada'])->assertSessionHasErrors('estado');
    expect($this->buyer->tieneTipo('vendedor'))->toBeFalse();
    foreach (['identificacion_frente', 'constancia_fiscal', 'comprobante_domicilio'] as $type) {
        $path = 'vendedores/'.$this->application->getKey().'/'.$type.'.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 documento de prueba');
        $this->application->documentos()->create(['tipo_documento' => $type, 'ruta_archivo' => $path, 'nombre_original' => $type.'.pdf', 'mime_type' => 'application/pdf', 'tamano' => 28]);
    }
    $document = $this->application->documentos()->first();
    $this->get(route('admin.solicitudes.show', $this->application))->assertOk()->assertSee('Identificación');
    $this->get(route('admin.solicitudes.document', [$this->application, $document]))->assertOk()->assertDownload($document->nombre_original);
    $this->post(route('admin.solicitudes.review', $this->application), ['estado' => 'aprobada'])->assertSessionHasNoErrors();
    expect($this->buyer->tieneTipo('vendedor'))->toBeTrue()->and($this->buyer->tieneTipo('comprador'))->toBeTrue()->and($this->application->fresh()->estado)->toBe('aprobada');
    $this->post(route('admin.solicitudes.review', $this->application), ['estado' => 'rechazada', 'motivo_revision' => 'Intento de revisión repetida'])->assertSessionHasErrors('estado');
    expect($this->buyer->notifications()->count())->toBe(1);
});

test('document download checks its application and denied buyers cannot download private documents', function () {
    $otherApplication = $this->admin->solicitudVendedor()->create(['estado' => 'en_revision']);
    Storage::disk('local')->put('vendedores/otro.pdf', '%PDF prueba');
    $document = $otherApplication->documentos()->create(['tipo_documento' => 'identificacion_frente', 'ruta_archivo' => 'vendedores/otro.pdf', 'nombre_original' => 'otro.pdf', 'mime_type' => 'application/pdf', 'tamano' => 10]);
    $this->actingAs($this->admin)->get(route('admin.solicitudes.document', [$this->application, $document]))->assertNotFound();
    $this->actingAs($this->buyer)->get(route('admin.solicitudes.document', [$otherApplication, $document]))->assertForbidden();
});

test('correction or rejection records the reason without granting seller access', function (string $state) {
    $this->actingAs($this->admin)->post(route('admin.solicitudes.review', $this->application), ['estado' => $state])->assertSessionHasErrors('motivo_revision');
    $this->post(route('admin.solicitudes.review', $this->application), ['estado' => $state, 'motivo_revision' => 'Necesitamos documentos legibles y actualizados.'])->assertSessionHasNoErrors();
    expect($this->application->fresh()->estado)->toBe($state)->and($this->buyer->tieneTipo('vendedor'))->toBeFalse()->and($this->buyer->notifications()->count())->toBe(1);
})->with(['requiere_correccion', 'rechazada']);
