<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Dashboard_Admin_Controller;
use App\Http\Controllers\TipoUsuarioController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

// HOME
Route::get('/', function () {
    return view('welcome');
});

// DASHBOARDS + RUTAS PROTEGIDAS
Route::middleware(['auth'])->group(function () {

    // Dashboard Comprador
    Route::prefix('comprador')->name('comprador.')->group(function () {
        Route::get('/dashboard', function () {
            return view('comprador.dashboard');
        })->name('dashboard');
    });

    // Dashboard Vendedor
    Route::prefix('vendedor')->name('vendedor.')->group(function () {
        Route::get('/dashboard', function () {
            return view('vendedor.dashboard');
        })->name('dashboard');
    });

    // Dashboard Administrador
    Route::prefix('administrador')->name('administrador.')->group(function () {
        Route::get('/dashboard', [Dashboard_Admin_Controller::class, 'index'])->name('dashboard');
    });

    // Tipos de Usuario
    Route::resource('tipos-usuario', TipoUsuarioController::class);

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::put('/password', [PasswordController::class, 'update'])
        ->name('password.update');

    // Email Verification
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('comprador.dashboard');
    })->middleware(['signed', 'throttle:6,1'])
      ->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')
      ->name('verification.send');
});

// AUTH LOGIN
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// REGISTER
Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

// PASSWORD RESET
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    return view('auth.reset-password', [
        'request' => $request,
        'token' => $token,
    ]);
})->middleware('guest')
  ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');