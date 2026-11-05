<?php

use App\Models\TiposUsuario;
use App\Models\User;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    $type = TiposUsuario::create(['nombre_tipo' => 'administrador']);
    $this->admin = User::create(['name' => 'Administración QA', 'email' => 'audit@example.test', 'password' => 'Audit-test-123!', 'activo' => true, 'email_verified_at' => now()]);
    $this->admin->tipos_usuario()->attach($type);
});

test('role management renders every resource view and preserves its backend protections', function () {
    $this->actingAs($this->admin)->get(route('tipos-usuario.index'))->assertOk()->assertSee('Tipos de cuenta');
    $this->get(route('tipos-usuario.create'))->assertOk()->assertSee('Crear tipo de cuenta');
    $this->post(route('tipos-usuario.store'), ['nombre_tipo' => 'Cuenta de auditoría'])->assertRedirect(route('tipos-usuario.index'));
    $type = TiposUsuario::where('nombre_tipo', 'Cuenta de auditoría')->firstOrFail();
    $this->get(route('tipos-usuario.show', $type))->assertOk()->assertSee('Confirmar eliminación');
    $this->get(route('tipos-usuario.edit', $type))->assertOk()->assertSee('Cuenta de auditoría');
    $this->put(route('tipos-usuario.update', $type), ['nombre_tipo' => 'Cuenta temporal'])->assertRedirect(route('tipos-usuario.index'));
    expect($type->fresh()->nombre_tipo)->toBe('Cuenta temporal');
    $this->delete(route('tipos-usuario.destroy', $type))->assertRedirect(route('tipos-usuario.index'));
    $this->assertDatabaseMissing('tipos_usuario', ['id_tipo_usuario' => $type->getKey()]);
    $assigned = $this->admin->tipos_usuario()->first();
    $this->delete(route('tipos-usuario.destroy', $assigned))->assertSessionHas('error');
    $this->assertDatabaseHas('tipos_usuario', ['id_tipo_usuario' => $assigned->getKey()]);
});

test('public plans use configured prices and do not promise an unimplemented free trial', function () {
    $this->get(route('vender'))->assertOk()->assertSee(config('subscriptions.plans.impulso.price'))
        ->assertSee(config('subscriptions.plans.pro.price'))->assertSee('Impulso')->assertSee('Pro')
        ->assertDontSee('14 días')->assertDontSee('href="#"', false);
});

test('password recovery and failed login communicate in Spanish even with an English app locale', function () {
    app()->setLocale('en');
    $this->get(route('password.request'))->assertOk()->assertSee('Recuperar contraseña')->assertSee('lang="es"', false);
    $this->from(route('login'))->post(route('login'), ['email' => $this->admin->email, 'password' => 'incorrecta'])
        ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos. Revisa tus datos e intenta de nuevo.']);
    $this->post(route('password.email'), ['email' => 'no-existe@example.test'])
        ->assertSessionHasErrors(['email' => 'No encontramos una cuenta con ese correo. Revisa la dirección o crea una cuenta.']);
});

test('a mail transport outage keeps recovery retryable without an error page', function () {
    \Illuminate\Support\Facades\Password::shouldReceive('sendResetLink')->once()
        ->with(['email' => $this->admin->email])
        ->andThrow(new \Symfony\Component\Mailer\Exception\TransportException('Simulated mail outage'));
    $this->from(route('password.request'))->post(route('password.email'), ['email' => $this->admin->email])
        ->assertRedirect(route('password.request'))
        ->assertSessionHasErrors(['email' => 'No pudimos enviar el correo de recuperación. Intenta de nuevo en unos momentos.'])
        ->assertSessionHasInput('email', $this->admin->email);
});
