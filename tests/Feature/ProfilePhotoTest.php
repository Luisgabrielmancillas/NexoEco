<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Storage::fake('local');
    $this->user = User::create(['name' => 'Usuario con foto', 'email' => 'foto@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
});

test('profile photo uploads replaces and removes only the current users image', function () {
    $this->actingAs($this->user)->get(route('profile.edit'))->assertOk()->assertSee('Mi foto de perfil')->assertDontSee('Mis favoritos');
    $this->post(route('profile.photo.store'), ['foto' => UploadedFile::fake()->image('foto.png', 128, 128)])->assertSessionHasNoErrors()->assertSessionHas('status', 'photo-updated');
    $first = $this->user->fresh()->profile_photo_path;
    Storage::disk('local')->assertExists($first);
    $this->get(route('profile.photo.show', $this->user))->assertOk()->assertHeader('Content-Type', 'image/png');
    $this->post(route('profile.photo.store'), ['foto' => UploadedFile::fake()->image('otra.png', 128, 128)])->assertSessionHasNoErrors();
    $current = $this->user->fresh()->profile_photo_path;
    expect($current)->not->toBe($first);
    Storage::disk('local')->assertMissing($first);
    Storage::disk('local')->assertExists($current);
    $this->delete(route('profile.photo.destroy'))->assertSessionHas('status', 'photo-removed');
    expect($this->user->fresh()->profile_photo_path)->toBeNull();
    Storage::disk('local')->assertMissing($current);
    $this->get(route('profile.photo.show', $this->user))->assertNotFound();
});

test('profile photos require authentication and reject unsafe or oversized files', function () {
    $this->get(route('profile.photo.show', $this->user))->assertRedirect(route('login'));
    $this->post(route('profile.photo.store'), ['foto' => UploadedFile::fake()->image('foto.png', 128, 128)])->assertRedirect(route('login'));
    $this->actingAs($this->user)->post(route('profile.photo.store'), ['foto' => UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml')])->assertSessionHasErrorsIn('profilePhoto', 'foto');
    $this->post(route('profile.photo.store'), ['foto' => UploadedFile::fake()->image('grande.jpg', 128, 128)->size(3073)])->assertSessionHasErrorsIn('profilePhoto', 'foto');
    $this->post(route('profile.photo.store'), ['foto' => UploadedFile::fake()->image('pequena.png', 10, 10)])->assertSessionHasErrorsIn('profilePhoto', 'foto');
    expect(Storage::disk('local')->allFiles())->toBeEmpty()->and($this->user->fresh()->profile_photo_path)->toBeNull();
});

test('a failed image replacement preserves the saved picture and removes its newly uploaded file', function () {
    $this->actingAs($this->user)->post(route('profile.photo.store'), ['foto' => UploadedFile::fake()->image('primera.png', 128, 128)])->assertSessionHasNoErrors();
    $previous = $this->user->fresh()->profile_photo_path;
    User::saving(function () {
        throw new RuntimeException('Simulated photo persistence failure');
    });
    try {
        $this->post(route('profile.photo.store'), ['foto' => UploadedFile::fake()->image('segunda.png', 128, 128)])->assertStatus(500);
        expect($this->user->fresh()->profile_photo_path)->toBe($previous)->and(Storage::disk('local')->allFiles())->toBe([$previous]);
    } finally {
        User::flushEventListeners();
    }
});

test('deleting an account removes its uploaded picture and account notifications', function () {
    $this->actingAs($this->user)->post(route('profile.photo.store'), ['foto' => UploadedFile::fake()->image('foto.png', 128, 128)])->assertSessionHasNoErrors();
    $photo = $this->user->fresh()->profile_photo_path;
    $this->user->notify(new \App\Notifications\BuyerActivityNotification('Aviso de prueba', 'Mensaje de prueba', route('profile.edit')));
    $this->delete(route('profile.destroy'), ['password' => 'clave-segura-123'])->assertRedirect('/');
    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('notifications', 0);
    Storage::disk('local')->assertMissing($photo);
});
