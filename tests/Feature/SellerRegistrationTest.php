<?php

use App\Models\DocumentoVendedor;
use App\Models\SolicitudVendedor;
use App\Models\TiposUsuario;
use App\Models\User;
use App\Notifications\VerifyNexoEcoEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    \Tests\Support\BuyerDatabase::migrate();
    Notification::fake();
    Storage::fake('local');
    TiposUsuario::create(['nombre_tipo' => 'comprador']);
    TiposUsuario::create(['nombre_tipo' => 'vendedor']);
});

function sellerAccountData(): array
{
    return [
        'nombre_completo' => 'María López',
        'email' => 'maria@example.test',
        'password' => 'contraseña-segura',
        'password_confirmation' => 'contraseña-segura',
    ];
}

function sellerDocumentData(): array
{
    return [
        'rfc' => 'LOPM900101AB1',
        'curp' => 'LOPM900101MDFPRS01',
        'telefono' => '5512345678',
        'domicilio_fiscal' => 'Calle Comunidad 123, Ciudad de México, 01000',
        'identificacion_frente' => UploadedFile::fake()->create('identificacion.pdf', 20, 'application/pdf'),
        'constancia_fiscal' => UploadedFile::fake()->create('constancia.pdf', 20, 'application/pdf'),
        'comprobante_domicilio' => UploadedFile::fake()->create('domicilio.pdf', 20, 'application/pdf'),
    ];
}

function sellerTestUser(bool $verified = false): User
{
    $user = User::create([
        'name' => 'María López',
        'nombre_completo' => 'María López',
        'email' => 'maria@example.test',
        'password' => 'contraseña-segura',
        'email_verified_at' => $verified ? now() : null,
        'activo' => true,
    ]);
    $user->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'comprador')->value('id_tipo_usuario'));

    return $user;
}

function verificationCodeFor(User $user): string
{
    $user->sendEmailVerificationNotification();

    return Notification::sent($user, VerifyNexoEcoEmail::class)->last()->code;
}

test('buyer registration has its own form and a seller entry on the left', function () {
    $this->get(route('register'))->assertOk()
        ->assertSee('Crear cuenta de comprador')
        ->assertSee('Vende con nosotros')
        ->assertSee(route('vendedor.register'))
        ->assertDontSee('name="tipo_registro"', false);
    $this->get('/vendedor/login')->assertNotFound();
    $this->get(route('login'))->assertOk()->assertSee('Iniciar sesión');
    $this->get(route('vendedor.register'))->assertOk()->assertSee('Datos fiscales')->assertSee('Documentos');
});

test('buyer registration creates no seller request', function () {
    $this->post(route('register'), sellerAccountData())->assertRedirect(route('verification.notice'));
    $user = User::firstOrFail();
    expect($user->tieneTipo('comprador'))->toBeTrue()
        ->and($user->tieneTipo('vendedor'))->toBeFalse();
    $this->assertDatabaseCount('solicitudes_vendedor', 0);
    Notification::assertSentTo($user, VerifyNexoEcoEmail::class);
});

test('buyer registration rejects attempts to request a seller account', function () {
    $this->post(route('register'), [...sellerAccountData(), 'tipo_registro' => 'vendedor'])
        ->assertSessionHasErrors('tipo_registro');
    $this->assertDatabaseCount('users', 0);
});

test('seller registration saves the account and private documents before email verification', function () {
    $this->post(route('vendedor.register'), [...sellerAccountData(), ...sellerDocumentData()])
        ->assertSessionHasNoErrors()->assertRedirect(route('vendedor.register'));
    $user = User::firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect($user->hasVerifiedEmail())->toBeFalse()
        ->and($user->tieneTipo('comprador'))->toBeTrue()
        ->and($user->tieneTipo('vendedor'))->toBeFalse()
        ->and($user->solicitudVendedor->estado)->toBe(SolicitudVendedor::ESTADO_EN_REVISION);
    $this->assertDatabaseCount('documentos_vendedor', 3);
    foreach ($user->solicitudVendedor->documentos as $document) {
        Storage::disk('local')->assertExists($document->ruta_archivo);
        expect($document->ruta_archivo)->toStartWith('vendedores/')
            ->and($document->hash_sha256)->toHaveLength(64);
    }
    $this->get(route('vendedor.register'))->assertOk()
        ->assertSee('Paso 04')->assertSee('Verifica tu correo electrónico')->assertSee('Tu información ya está guardada');
    $this->get(route('verification.notice'))->assertRedirect(route('vendedor.register'));
    Notification::assertSentTo($user, VerifyNexoEcoEmail::class);
    $code = Notification::sent($user, VerifyNexoEcoEmail::class)->last()->code;
    $this->post(route('verification.verify'), ['codigo' => $code])->assertRedirect(route('comprador.dashboard'));
    $this->get(route('comprador.dashboard'))->assertOk()->assertSee('Tu cuenta de vendedor está en revisión');
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()->and($user->tieneTipo('vendedor'))->toBeFalse();
});

test('seller registration rejects missing documents without creating an account', function () {
    $data = [...sellerAccountData(), ...sellerDocumentData()];
    unset($data['constancia_fiscal']);
    $this->post(route('vendedor.register'), $data)->assertSessionHasErrors('constancia_fiscal');
    $this->assertDatabaseCount('users', 0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('seller registration rejects invalid files and does not flash passwords', function () {
    $this->post(route('vendedor.register'), [
        ...sellerAccountData(), ...sellerDocumentData(),
        'identificacion_frente' => UploadedFile::fake()->create('script.exe', 20, 'application/octet-stream'),
        'constancia_fiscal' => UploadedFile::fake()->create('grande.pdf', 5121, 'application/pdf'),
    ])->assertSessionHasErrors(['identificacion_frente', 'constancia_fiscal'])
        ->assertSessionMissing('_old_input.password')->assertSessionMissing('_old_input.password_confirmation');
    $this->assertDatabaseCount('users', 0);
});

test('unverified buyers can complete the seller documents in the same flow', function () {
    $user = sellerTestUser();
    $this->actingAs($user)->post(route('vendedor.solicitud.store'), sellerDocumentData())
        ->assertSessionHasNoErrors()->assertRedirect(route('vendedor.register'));
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseCount('documentos_vendedor', 3);
    Notification::assertSentTo($user, VerifyNexoEcoEmail::class);
});

test('general login resumes verification and allows completing a legacy seller application', function () {
    $user = sellerTestUser();
    $user->solicitudVendedor()->create(['estado' => SolicitudVendedor::ESTADO_PENDIENTE_DOCUMENTOS]);
    $this->post(route('login'), ['email' => $user->email, 'password' => 'contraseña-segura'])
        ->assertRedirect(route('verification.notice'));
    $this->get(route('vendedor.register'))->assertOk()->assertSee('data-account-complete="true"', false)
        ->assertDontSee('name="password"', false);
});

test('email verification sends pending sellers to the buyer dashboard without activating sales', function () {
    $user = sellerTestUser();
    $user->solicitudVendedor()->create(['estado' => SolicitudVendedor::ESTADO_EN_REVISION]);
    $code = verificationCodeFor($user);
    $this->actingAs($user)->post(route('verification.verify'), ['codigo' => $code])->assertRedirect(route('comprador.dashboard'));
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()->and($user->tieneTipo('vendedor'))->toBeFalse();
    $this->get(route('comprador.dashboard'))->assertOk()->assertSee('Tu cuenta de vendedor está en revisión');
    $this->get(route('vendedor.dashboard'))->assertForbidden();
});

test('email verification rejects wrong and expired codes', function () {
    $user = sellerTestUser();
    $code = verificationCodeFor($user);
    $wrong = $code === '0000' ? '1111' : '0000';
    $this->actingAs($user)->post(route('verification.verify'), ['codigo' => $wrong])->assertSessionHasErrors('codigo');
    $this->travel(11)->minutes();
    $this->post(route('verification.verify'), ['codigo' => $code])->assertSessionHasErrors('codigo');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('requests under review cannot be overwritten', function () {
    $user = sellerTestUser(true);
    $user->solicitudVendedor()->create(['estado' => SolicitudVendedor::ESTADO_EN_REVISION, 'rfc' => 'ORIGINAL']);
    $this->actingAs($user)->post(route('vendedor.solicitud.store'), sellerDocumentData())->assertSessionHasErrors('documentos');
    expect($user->solicitudVendedor->rfc)->toBe('ORIGINAL');
    $this->assertDatabaseCount('documentos_vendedor', 0);
});

test('database failures roll back new seller accounts and remove uploaded documents', function () {
    DocumentoVendedor::creating(function ($document) {
        if ($document->tipo_documento === 'constancia_fiscal') {
            throw new RuntimeException('Simulated document persistence failure');
        }
    });
    try {
        $this->post(route('vendedor.register'), [...sellerAccountData(), ...sellerDocumentData()])->assertSessionHasErrors('documentos');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('solicitudes_vendedor', 0);
        $this->assertDatabaseCount('documentos_vendedor', 0);
        expect(Storage::disk('local')->allFiles())->toBeEmpty();
    } finally {
        DocumentoVendedor::flushEventListeners();
    }
});

test('the verification email shows four digits with Spanish branding and no verification link', function () {
    $user = sellerTestUser();
    $user->solicitudVendedor()->create(['estado' => SolicitudVendedor::ESTADO_EN_REVISION]);
    $message = (new VerifyNexoEcoEmail('0123'))->toMail($user);
    $html = $message->render();
    expect($message->subject)->toBe('Verifica tu correo para vender en NexoEco')
        ->and((string) $html)->toContain('0123', '#E85D2F', '#FAF7F2', 'María López', 'Tu cuenta de vendedor está en revisión')
        ->and((string) $html)->not->toContain('signature=', 'Verificar mi correo');
});

test('verification codes require authentication and belong only to the recipient', function () {
    $user = sellerTestUser();
    $code = verificationCodeFor($user);
    $this->post(route('verification.verify'), ['codigo' => $code])->assertRedirect(route('login'));
    $other = User::create([
        'name' => 'Otra cuenta', 'email' => 'otra@example.test', 'password' => 'clave-segura', 'activo' => true,
    ]);
    $this->actingAs($other)->post(route('verification.verify'), ['codigo' => $code])->assertSessionHasErrors('codigo');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse()->and($other->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('approved sellers use the general login', function () {
    $user = sellerTestUser(true);
    $user->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'vendedor')->value('id_tipo_usuario'));
    $this->post(route('login'), ['email' => $user->email, 'password' => 'contraseña-segura'])
        ->assertRedirect(route('comprador.dashboard'));
    $this->get(route('vendedor.register'))->assertRedirect(route('vendedor.dashboard'));
    $this->get(route('vendedor.dashboard'))->assertRedirect(route('vendedor.tienda.create'));
});

test('failed corrections keep previous documents and remove only newly uploaded files', function () {
    $user = sellerTestUser(true);
    $solicitud = $user->solicitudVendedor()->create(['estado' => SolicitudVendedor::ESTADO_REQUIERE_CORRECCION]);
    $path = 'vendedores/'.$solicitud->id_solicitud.'/previous.pdf';
    Storage::disk('local')->put($path, 'Previous document');
    $solicitud->documentos()->create([
        'tipo_documento' => 'identificacion_frente', 'ruta_archivo' => $path,
        'nombre_original' => 'previous.pdf', 'mime_type' => 'application/pdf', 'tamano' => 17,
    ]);
    DocumentoVendedor::creating(function ($document) {
        if ($document->tipo_documento === 'constancia_fiscal') {
            throw new RuntimeException('Simulated correction failure');
        }
    });
    try {
        $this->actingAs($user)->post(route('vendedor.solicitud.store'), sellerDocumentData())->assertSessionHasErrors('documentos');
        Storage::disk('local')->assertExists($path);
        expect(Storage::disk('local')->allFiles())->toBe([$path])
            ->and($solicitud->fresh()->estado)->toBe(SolicitudVendedor::ESTADO_REQUIERE_CORRECCION)
            ->and($solicitud->documentos()->first()->ruta_archivo)->toBe($path);
    } finally {
        DocumentoVendedor::flushEventListeners();
    }
});

test('seller registration remains saved when sending the verification email fails', function () {
    Notification::shouldReceive('send')->with(Mockery::any(), Mockery::type(\App\Notifications\BuyerActivityNotification::class))->once()->andReturnNull();
    Notification::shouldReceive('send')->with(Mockery::any(), Mockery::type(VerifyNexoEcoEmail::class))->once()->andThrow(new RuntimeException('Simulated SMTP failure'));
    $this->post(route('vendedor.register'), [...sellerAccountData(), ...sellerDocumentData()])
        ->assertRedirect(route('vendedor.register'))->assertSessionHas('status', 'verification-send-failed');
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseCount('documentos_vendedor', 3);
    $this->get(route('vendedor.register'))->assertOk()->assertSee('Tu información está guardada');
});

test('buyers can retry email verification when sending the email fails', function () {
    Notification::shouldReceive('send')->once()->andThrow(new RuntimeException('Simulated SMTP failure'));
    $this->post(route('register'), sellerAccountData())
        ->assertRedirect(route('verification.notice'))->assertSessionHas('status', 'verification-send-failed');
    $this->assertAuthenticatedAs(User::firstOrFail());
    $this->get(route('verification.notice'))->assertOk()->assertSee('No pudimos enviar el correo de verificación.');
});

test('buyer registration verifies a code and goes directly to the buyer dashboard', function () {
    Event::fake([Verified::class]);
    $this->post(route('register'), sellerAccountData())->assertRedirect(route('verification.notice'));
    $user = User::firstOrFail();
    $code = Notification::sent($user, VerifyNexoEcoEmail::class)->last()->code;
    expect($code)->toMatch('/^[0-9]{4}$/');
    $this->get(route('verification.notice'))->assertOk()->assertSee('name="codigo"', false);
    $this->post(route('verification.verify'), ['codigo' => $code])->assertRedirect(route('comprador.dashboard'));
    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $this->get(route('comprador.dashboard'))->assertOk()->assertDontSee('Tu cuenta de vendedor está en revisión');
});

test('resending a code invalidates the previous one', function () {
    $user = sellerTestUser();
    $first = verificationCodeFor($user);
    $this->actingAs($user)->post(route('verification.send'))->assertSessionHas('status', 'verification-code-sent');
    $latest = Notification::sent($user, VerifyNexoEcoEmail::class)->last()->code;
    expect($latest)->not->toBe($first);
    $this->post(route('verification.verify'), ['codigo' => $first])->assertSessionHasErrors('codigo');
    $this->post(route('verification.verify'), ['codigo' => $latest])->assertRedirect(route('comprador.dashboard'));
});

test('codes preserve leading zeros and are stored hashed and consumed on success', function () {
    $user = sellerTestUser();
    Cache::put('email-verification-code:'.$user->id, ['email' => $user->email, 'hash' => Hash::make('0123')], now()->addMinutes(10));
    $this->actingAs($user)->post(route('verification.verify'), ['codigo' => '0123'])->assertRedirect(route('comprador.dashboard'));
    expect(Cache::has('email-verification-code:'.$user->id))->toBeFalse();
    $user->update(['email_verified_at' => null]);
    $this->post(route('verification.verify'), ['codigo' => '0123'])->assertSessionHasErrors('codigo');
});

test('five failed attempts block further guesses even after resending', function () {
    $user = sellerTestUser();
    $code = verificationCodeFor($user);
    $wrong = $code === '0000' ? '1111' : '0000';
    $this->actingAs($user);
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('verification.verify'), ['codigo' => $wrong])->assertSessionHasErrors('codigo');
    }
    $fresh = verificationCodeFor($user);
    $this->post(route('verification.verify'), ['codigo' => $fresh])->assertSessionHasErrors('codigo');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verified pending sellers log in to the buyer dashboard and retain the review message', function () {
    $user = sellerTestUser(true);
    $user->solicitudVendedor()->create(['estado' => SolicitudVendedor::ESTADO_EN_REVISION]);
    $this->post(route('login'), ['email' => $user->email, 'password' => 'contraseña-segura'])->assertRedirect(route('comprador.dashboard'));
    $this->get(route('comprador.dashboard'))->assertOk()->assertSee('Tu cuenta de vendedor está en revisión');
    $this->get(route('comprador.dashboard'))->assertOk()->assertSee('Tu cuenta de vendedor está en revisión');
    expect($user->tieneTipo('comprador'))->toBeTrue()->and($user->tieneTipo('vendedor'))->toBeFalse();
});

test('codes for a previous email cannot verify a changed email', function () {
    $user = sellerTestUser();
    $code = verificationCodeFor($user);
    $user->update(['email' => 'cambio@example.test']);
    $this->actingAs($user)->post(route('verification.verify'), ['codigo' => $code])->assertSessionHasErrors('codigo');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification requires exactly four numeric characters', function (string $code) {
    $user = sellerTestUser();
    verificationCodeFor($user);
    $this->actingAs($user)->post(route('verification.verify'), ['codigo' => $code])->assertSessionHasErrors('codigo');
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
})->with(['', '123', '12345', 'abcd', '12 3']);

test('issued codes are stored only as hashes', function () {
    $user = sellerTestUser();
    $code = verificationCodeFor($user);
    $record = Cache::get('email-verification-code:'.$user->id);
    expect($record['hash'])->not->toBe($code)
        ->and(Hash::check($code, $record['hash']))->toBeTrue();
});

test('the general login supports administrative and moderator roles', function (string $role, string $dashboard) {
    $user = sellerTestUser(true);
    $type = TiposUsuario::create(['nombre_tipo' => $role]);
    $user->tipos_usuario()->attach($type->id_tipo_usuario);
    $this->post(route('login'), ['email' => $user->email, 'password' => 'contraseña-segura'])->assertRedirect(route($dashboard));
})->with([
    ['administrador', 'administrador.dashboard'],
    ['moderador', 'moderador.dashboard'],
]);

test('old verification links no longer verify an account', function () {
    $user = sellerTestUser();
    $this->actingAs($user)->get('/email/verify/'.$user->id.'/'.sha1($user->email))->assertNotFound();
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
