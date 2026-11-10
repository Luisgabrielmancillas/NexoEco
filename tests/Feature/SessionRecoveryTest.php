<?php

use App\Http\Middleware\ValidateCsrfToken;
use App\Models\TiposUsuario;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    $role = TiposUsuario::create(['nombre_tipo' => 'comprador']);
    $this->account = User::create(['name' => 'Sesión', 'email' => 'session@example.test', 'password' => 'password', 'email_verified_at' => now(), 'activo' => true]);
    $this->account->tipos_usuario()->attach($role);

    // Laravel normally skips CSRF in tests; exercise the real mismatch path.
    $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
    {
        protected function runningUnitTests()
        {
            return false;
        }
    });
});

test('stale logout from an authenticated tab offers a fresh confirmation without bypassing CSRF', function () {
    $this->actingAs($this->account)->withSession(['_token' => 'current-token'])
        ->post(route('logout'), ['_token' => 'old-token'])
        ->assertStatus(419)->assertViewIs('errors.419')->assertViewHas('logoutRecovery', true)
        ->assertSee('Confirma el cierre de sesión')->assertSee('value="current-token"', false)
        ->assertHeader('Cache-Control', 'no-store, private')->assertCookie(config('session.cookie'));
    $this->assertAuthenticatedAs($this->account);

    $this->post(route('logout'), ['_token' => 'current-token'])->assertRedirect('/');
    $this->assertGuest();
    expect(session()->token())->not->toBe('current-token');
});

test('expired or repeated logout with an invalid token returns guests to the home page', function () {
    $this->withSession(['_token' => 'new-session-token'])->post(route('logout'), ['_token' => 'expired-token'])
        ->assertRedirect('/')->assertHeader('Cache-Control', 'no-store, private');
    $this->assertGuest();
});

test('other stale forms show an app recovery dialog without repeating the action', function () {
    $this->actingAs($this->account)->withSession(['_token' => 'current-token'])
        ->patch(route('profile.update'), ['_token' => 'old-token', 'name' => 'Cambio no autorizado', 'email' => $this->account->email])
        ->assertStatus(419)->assertViewHas('logoutRecovery', false)->assertSee('Actualiza tu sesión')
        ->assertSee('data-session-recovery', false)->assertDontSee('Cerrar sesión');
    expect($this->account->fresh()->name)->toBe('Sesión');
});

test('JSON requests retain an error response and CSRF remains enforced', function () {
    $this->actingAs($this->account)->withSession(['_token' => 'current-token'])
        ->postJson(route('logout'), ['_token' => 'old-token'])
        ->assertStatus(419)->assertJsonStructure(['message']);
    $this->assertAuthenticatedAs($this->account);
});

test('a remembered account receives a persisted session cookie before confirming logout', function () {
    $this->account->forceFill(['remember_token' => 'remember-token'])->save();
    $guard = Auth::guard('web');
    $recaller = $this->account->id.'|remember-token|'.$this->account->getAuthPassword();
    $this->withCookie($guard->getRecallerName(), $recaller)->withSession(['_token' => 'fresh-token'])
        ->post(route('logout'), ['_token' => 'expired-token'])
        ->assertStatus(419)->assertSee('Confirma el cierre de sesión')
        ->assertCookie(config('session.cookie'))->assertSee('value="'.session()->token().'"', false);
    $this->assertAuthenticatedAs($this->account);
    expect($guard->viaRemember())->toBeTrue()->and(session($guard->getName()))->toBe($this->account->id);
});
