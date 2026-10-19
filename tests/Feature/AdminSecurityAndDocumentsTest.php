<?php

use App\Models\RegistroActividad;
use App\Models\TiposUsuario;
use App\Models\User;
use App\Services\AdministratorLimit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Storage::fake('local');
    foreach (['vendedor', 'Administrador', 'Comprador', 'Moderador'] as $role) {
        TiposUsuario::create(['nombre_tipo' => $role]);
    }
    $this->admin = User::create(['name' => 'Administradora de prueba', 'nombre_completo' => 'Administradora de prueba', 'email' => 'admin@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
    $this->admin->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'Administrador')->value('id_tipo_usuario'));
    $this->buyer = User::create(['name' => 'Comprador de prueba', 'nombre_completo' => 'Comprador de prueba', 'email' => 'buyer@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'Comprador')->value('id_tipo_usuario'));
    $this->application = $this->buyer->solicitudVendedor()->create(['estado' => 'en_revision', 'rfc' => 'LOPM900101AB1']);
});

function additionalAdmin(int $number, bool $active = true): User
{
    $user = User::create(['name' => 'Administrador '.$number, 'email' => 'admin-'.$number.'@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => $active]);
    $user->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'Administrador')->value('id_tipo_usuario'));

    return $user;
}

test('a third administrator can be created but a fourth is rejected including inactive accounts', function () {
    additionalAdmin(2, false);
    $data = ['name' => 'Tercer administrador', 'email' => 'tercero@example.test', 'password' => 'clave-segura-123', 'rol' => 'administrador'];
    $this->actingAs($this->admin)->post(route('admin.users.store'), $data)->assertSessionHasNoErrors();
    expect(app(AdministratorLimit::class)->count())->toBe(3);
    $this->postJson(route('admin.users.store'), array_replace($data, ['email' => 'cuarto@example.test']))->assertUnprocessable()->assertJsonValidationErrors('rol');
    expect(User::where('email', 'cuarto@example.test')->exists())->toBeFalse()->and(RegistroActividad::count())->toBe(1);
    $response = $this->get(route('admin.users.create', ['rol' => 'administrador']))->assertOk()->assertSee('Administradores: 3/3')->assertSee('Límite alcanzado');
    expect($response->viewData('adminCount'))->toBe(3);
    $this->post(route('admin.users.store'), array_replace($data, ['name' => 'Comprador permitido', 'email' => 'permitido@example.test', 'rol' => 'comprador']))->assertSessionHasNoErrors();
    expect(app(AdministratorLimit::class)->count())->toBe(3);
});

test('administrator capacity is checked again inside the creation transaction after a stale form check', function () {
    additionalAdmin(2);
    additionalAdmin(3);
    app()->instance(AdministratorLimit::class, new class extends AdministratorLimit
    {
        public function count(): int
        {
            return 2;
        }
    });
    $this->actingAs($this->admin)->post(route('admin.users.store'), ['name' => 'No debe crearse', 'email' => 'cuarto@example.test', 'password' => 'clave-segura-123', 'rol' => 'administrador'])->assertSessionHasErrors('rol');
    expect(User::where('email', 'cuarto@example.test')->exists())->toBeFalse();
    $this->assertDatabaseCount('registros_actividad', 0);
});

test('role edits cannot bypass the administrator limit or remove the administrative role', function () {
    additionalAdmin(2);
    additionalAdmin(3);
    $role = TiposUsuario::where('nombre_tipo', 'Moderador')->firstOrFail();
    $moderator = User::create(['name' => 'Moderador', 'email' => 'mod@example.test', 'password' => 'clave-segura-123', 'activo' => true]);
    $moderator->tipos_usuario()->attach($role);
    $this->actingAs($this->admin)->put(route('tipos-usuario.update', $role), ['nombre_tipo' => 'ADMINISTRADOR'])->assertSessionHasErrors('nombre_tipo');
    expect($moderator->tieneTipo('administrador'))->toBeFalse()->and($role->fresh()->nombre_tipo)->toBe('Moderador');
    $adminRole = TiposUsuario::where('nombre_tipo', 'Administrador')->firstOrFail();
    $this->put(route('tipos-usuario.update', $adminRole), ['nombre_tipo' => 'otro'])->assertSessionHasErrors('nombre_tipo');
    $this->put(route('admin.users.update', $this->buyer), ['name' => $this->buyer->name, 'email' => $this->buyer->email, 'rol' => 'administrador'])->assertSessionHasNoErrors();
    expect($this->buyer->tieneTipo('administrador'))->toBeFalse()->and(app(AdministratorLimit::class)->count())->toBe(3);
});

test('deactivation keeps an administrator slot and duplicate administrator roles count each account once', function () {
    $second = additionalAdmin(2);
    additionalAdmin(3);
    $duplicate = TiposUsuario::create(['nombre_tipo' => 'ADMINISTRADOR']);
    $this->admin->tipos_usuario()->attach($duplicate);
    expect(app(AdministratorLimit::class)->count())->toBe(3);
    $this->actingAs($this->admin)->delete(route('admin.users.destroy', $second))->assertSessionHas('success');
    expect(app(AdministratorLimit::class)->count())->toBe(3);
    $this->post(route('admin.users.store'), ['name' => 'Cuarto', 'email' => 'cuarto@example.test', 'password' => 'clave-segura-123', 'rol' => 'administrador'])->assertSessionHasErrors('rol');
});

test('summary hides old navigation records and duplicate pending sections while keeping important changes', function () {
    RegistroActividad::create(['id_usuario' => $this->admin->id, 'nombre_actor' => $this->admin->name, 'roles_actor' => ['administrador'], 'accion' => 'Consultó una cuenta', 'descripcion' => 'Navegación antigua que debe ocultarse', 'ruta' => 'admin.users.show', 'metodo' => 'GET', 'registrada_en' => now()]);
    $this->actingAs($this->admin)->get(route('admin.users.index'))->assertOk();
    $this->get(route('admin.users.show', $this->buyer))->assertOk();
    $this->get(route('admin.solicitudes.show', $this->application))->assertOk();
    $this->put(route('admin.users.update', $this->buyer), ['name' => 'Nombre cambiado', 'email' => 'nuevo@example.test'])->assertSessionHasNoErrors();
    $activity = RegistroActividad::latest('id')->first();
    expect($activity->descripcion)->toContain('nombre, correo')->toContain('Nombre cambiado');
    $response = $this->get(route('administrador.dashboard'))->assertOk()->assertDontSee('Navegación antigua que debe ocultarse')->assertDontSee('Consultó una cuenta')
        ->assertDontSee('Consultas pendientes')->assertDontSee('Revisar solicitudes')->assertSee('Consultas por atender')->assertSee('Vendedores en revisión')->assertSee('Nombre cambiado');
    expect($response->viewData('recentActivities')->total())->toBe(1);
    $this->put(route('admin.users.update', $this->buyer), ['name' => 'Nombre cambiado', 'email' => 'nuevo@example.test'])->assertSessionHasNoErrors();
    expect(RegistroActividad::important()->count())->toBe(1);
});

test('seller document previews are private inline and lazy-loaded for PDFs and images', function (string $format) {
    if ($format === 'pdf') {
        $path = 'vendedores/prueba.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 documento de prueba');
        $mime = 'application/pdf';
    } else {
        $path = UploadedFile::fake()->image('prueba.png', 128, 128)->store('vendedores', 'local');
        $mime = 'image/png';
    }
    $document = $this->application->documentos()->create(['tipo_documento' => 'identificacion_frente', 'ruta_archivo' => $path, 'nombre_original' => 'identificación.'.$format, 'mime_type' => $mime, 'tamano' => 128]);
    $url = route('admin.solicitudes.preview', [$this->application, $document]);
    $this->get($url)->assertRedirect(route('login'));
    $this->actingAs($this->buyer)->get($url)->assertForbidden();
    $this->actingAs($this->admin)->get($url)->assertOk()->assertHeader('Content-Type', $mime)->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($this->get($url)->headers->get('Content-Disposition'))->toStartWith('inline;');
    $this->get(route('admin.solicitudes.show', $this->application))->assertOk()->assertSee('data-document-preview', false)->assertSee('data-preview-src="'.$url.'"', false)->assertDontSee(' src="'.$url.'"', false);
    $download = $this->get(route('admin.solicitudes.document', [$this->application, $document]))->assertOk()->assertDownload();
    expect($download->headers->get('Content-Disposition'))->toContain("filename*=utf-8''".rawurlencode($document->nombre_original));
    $this->assertDatabaseCount('registros_actividad', 0);
})->with(['pdf', 'png']);

test('document previews reject mismatched applications missing files and disguised active content', function () {
    $other = $this->admin->solicitudVendedor()->create(['estado' => 'en_revision']);
    Storage::disk('local')->put('vendedores/falso.pdf', '<html><script>alert(1)</script></html>');
    $document = $other->documentos()->create(['tipo_documento' => 'identificacion_frente', 'ruta_archivo' => 'vendedores/falso.pdf', 'nombre_original' => 'falso.pdf', 'mime_type' => 'application/pdf', 'tamano' => 50]);
    $this->actingAs($this->admin)->get(route('admin.solicitudes.preview', [$this->application, $document]))->assertNotFound();
    $this->get(route('admin.solicitudes.preview', [$other, $document]))->assertStatus(415);
    Storage::disk('local')->delete('vendedores/falso.pdf');
    $this->get(route('admin.solicitudes.preview', [$other, $document]))->assertNotFound();
    $this->admin->update(['email_verified_at' => null]);
    $this->get(route('admin.solicitudes.preview', [$other, $document]))->assertRedirect(route('verification.notice'));
});
