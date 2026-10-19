<?php

use App\Models\RegistroActividad;
use App\Models\TiposUsuario;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Storage::fake('local');
    foreach (['comprador', 'administrador', 'moderador', 'vendedor'] as $role) {
        TiposUsuario::create(['nombre_tipo' => $role]);
    }
    foreach (['admin' => 'administrador', 'moderator' => 'moderador', 'buyer' => 'comprador'] as $property => $role) {
        $user = User::create(['name' => ucfirst($property).' de prueba', 'email' => $property.'@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
        $user->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', $role)->value('id_tipo_usuario'));
        $this->{$property} = $user;
    }
});

test('summary removes account creation catalog and opinions and moves latest accounts into role-based users', function () {
    $response = $this->actingAs($this->admin)->get(route('administrador.dashboard'))->assertOk()->assertSee('Acciones recientes')
        ->assertDontSee('Últimas cuentas registradas')->assertDontSee('Crear cuenta rápida')->assertDontSee('Crear comprador, moderador o administrador')
        ->assertDontSee('href="'.route('admin.catalogo', 'productos').'"', false)->assertDontSee('href="'.route('admin.opiniones').'"', false)
        ->assertDontSee('<details', false)->assertSee('href="'.route('admin.users.index').'"', false);
    expect($response->viewData('recentActivities')->total())->toBe(0);
    $response = $this->get(route('admin.users.index', ['rol' => 'comprador']))->assertOk()->assertSee('Usuarios por rol')->assertSee('Últimas cuentas registradas')->assertSee('Crear cuenta rápida')
        ->assertSee('href="'.route('admin.users.show', $this->buyer).'"', false)->assertSee('href="'.route('admin.users.edit', $this->buyer).'"', false)
        ->assertSee('class="admin-action-button danger"', false)->assertDontSee('Ver / editar');
    expect($response->viewData('recentUsers')->modelKeys())->toBe([$this->buyer->id])
        ->and($response->viewData('roleCounts')['comprador'])->toBe(1);
    $this->get(route('admin.users.show', $this->buyer))->assertOk()->assertSee($this->buyer->email)->assertSee('Editar cuenta')->assertDontSee('name="password"', false);
});

test('staff account changes persist an authenticated actor and timestamp without storing posted credentials', function () {
    $this->travelTo(Carbon::parse('2026-10-08 17:42:35', 'UTC'));
    $this->actingAs($this->admin)->post(route('admin.users.store'), ['name' => 'Nueva moderadora', 'email' => 'nueva@example.test', 'password' => 'contraseña-no-debe-registrarse', 'rol' => 'moderador', 'id_usuario' => $this->buyer->id, 'staff_activity' => ['accion' => 'Acción falsificada']])->assertSessionHasNoErrors();
    $created = User::where('email', 'nueva@example.test')->firstOrFail();
    $activity = RegistroActividad::sole();
    expect($activity->id_usuario)->toBe($this->admin->id)->and($activity->nombre_actor)->toBe($this->admin->name)
        ->and($activity->roles_actor)->toBe(['administrador'])->and($activity->accion)->toBe('Creó una cuenta')
        ->and($activity->descripcion)->toContain('Nueva moderadora')->and($activity->id_objeto)->toBe((string) $created->id)
        ->and($activity->registrada_en->equalTo(now()))->toBeTrue()->and(json_encode($activity->toArray()))->not->toContain('contraseña-no-debe-registrarse');
    $this->put(route('admin.users.update', $created), ['name' => 'Nombre corregido', 'email' => 'corregido@example.test'])->assertSessionHasNoErrors();
    $this->delete(route('admin.users.destroy', $created))->assertSessionHas('success');
    $this->delete(route('admin.users.destroy', $created))->assertSessionHas('success');
    expect(RegistroActividad::orderBy('id')->pluck('accion')->all())->toBe(['Creó una cuenta', 'Actualizó una cuenta', 'Desactivó una cuenta', 'Reactivó una cuenta']);
    $this->get(route('administrador.dashboard'))->assertOk()->assertSee('Nueva moderadora')->assertSee($this->admin->name)->assertSee('08/10/2026')->assertSee('11:42:35');
    $this->travelBack();
});

test('invalid forbidden and no-op account actions do not appear as completed staff activity', function () {
    $this->actingAs($this->admin)->post(route('admin.users.store'), ['name' => 'Inválida', 'email' => $this->buyer->email, 'password' => 'corta', 'rol' => 'administrador'])->assertSessionHasErrors();
    $this->delete(route('admin.users.destroy', $this->admin))->assertSessionHas('error');
    $this->actingAs($this->moderator)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($this->buyer)->get(route('administrador.dashboard'))->assertForbidden();
    $this->get(route('comprador.dashboard'))->assertOk();
    $this->assertDatabaseCount('registros_actividad', 0);
    // A previous validation error must not suppress a later valid action.
    $this->actingAs($this->admin)->post(route('admin.users.store'), ['name' => 'Válida', 'email' => 'valida@example.test', 'password' => 'clave-segura-123', 'rol' => 'comprador'])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('registros_actividad', 1);
});

test('moderator and administrator personal changes are recorded without navigation or session noise', function (string $property, string $role) {
    $user = $this->{$property};
    $this->post(route('login'), ['email' => $user->email, 'password' => 'clave-segura-123'])->assertRedirect();
    $this->get(route($role === 'moderador' ? 'moderador.configuracion' : 'profile.edit'))->assertOk();
    $this->patch(route('profile.update'), ['name' => 'Nombre actualizado', 'email' => $user->email])->assertSessionHasNoErrors();
    $this->put(route('password.update'), ['current_password' => 'clave-segura-123', 'password' => 'nueva-clave-segura', 'password_confirmation' => 'nueva-clave-segura'])->assertSessionHasNoErrors();
    $this->post(route('logout'))->assertRedirect(route('marketplace.index'));
    expect(RegistroActividad::orderBy('id')->pluck('accion')->all())->toBe(['Actualizó sus datos personales', 'Cambió su contraseña'])
        ->and(RegistroActividad::where('id_usuario', $user->id)->count())->toBe(2)
        ->and(RegistroActividad::first()->roles_actor)->toBe([$role]);
    $this->actingAs($this->admin)->get(route('administrador.dashboard'))->assertOk()->assertSee('Cambió su contraseña')->assertSee('Nombre actualizado');
})->with([['admin', 'administrador'], ['moderator', 'moderador']]);

test('support and seller review outcomes are audited without copying private messages or documents', function () {
    $ticket = $this->buyer->solicitudesSoporte()->create(['asunto' => 'Consulta de prueba', 'mensaje' => 'Mensaje privado del comprador']);
    $application = $this->buyer->solicitudVendedor()->create(['estado' => 'en_revision', 'rfc' => 'LOPM900101AB1']);
    $this->actingAs($this->admin)->post(route('admin.consultas.reply', $ticket), ['mensaje' => 'Respuesta privada que no debe copiarse al historial', 'estado' => 'cerrada'])->assertSessionHasNoErrors();
    $this->post(route('admin.solicitudes.review', $application), ['estado' => 'requiere_correccion', 'motivo_revision' => 'Motivo privado que no debe copiarse al historial'])->assertSessionHasNoErrors();
    expect(RegistroActividad::orderBy('id')->pluck('accion')->all())->toBe(['Respondió una consulta', 'Solicitó correcciones al vendedor'])
        ->and(RegistroActividad::first()->descripcion)->toContain('resuelta')
        ->and(RegistroActividad::latest('id')->first()->id_objeto)->toBe((string) $application->getKey())
        ->and(RegistroActividad::all()->toJson())->not->toContain('Respuesta privada')->not->toContain('Motivo privado')->not->toContain('LOPM900101AB1');
});

test('activity feed paginates and preserves identity after a staff account is deleted', function () {
    $this->actingAs($this->moderator)->get(route('moderador.configuracion'))->assertOk();
    $originalName = $this->moderator->name;
    $this->patch(route('profile.update'), ['name' => 'Moderador renombrado', 'email' => $this->moderator->email])->assertSessionHasNoErrors();
    $this->delete(route('profile.destroy'), ['password' => 'clave-segura-123'])->assertRedirect();
    expect(RegistroActividad::orderBy('id')->first()->nombre_actor)->toBe($originalName)
        ->and(RegistroActividad::whereNotNull('id_usuario')->count())->toBe(0)
        ->and(RegistroActividad::latest('id')->first()->accion)->toBe('Eliminó su cuenta');
    foreach (range(1, 10) as $number) {
        $this->actingAs($this->admin)->put(route('admin.users.update', $this->buyer), ['name' => 'Nombre actualizado '.$number, 'email' => $this->buyer->email])->assertSessionHasNoErrors();
    }
    $response = $this->get(route('administrador.dashboard'))->assertOk()->assertSee('Acciones recientes');
    expect($response->viewData('recentActivities')->total())->toBe(12)->and($response->viewData('recentActivities')->count())->toBe(10);
    $response = $this->get(route('administrador.dashboard', ['pagina_actividad' => 2]))->assertOk()->assertSee($originalName)->assertSee('Eliminó su cuenta');
    expect($response->viewData('recentActivities')->currentPage())->toBe(2);
});

test('private user details remain protected from non-administrative roles', function (string $property) {
    $this->actingAs($this->{$property})->get(route('admin.users.show', $this->admin))->assertForbidden();
})->with(['buyer', 'moderator']);
