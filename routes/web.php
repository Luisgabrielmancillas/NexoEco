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
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\TiendaController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| MARKETPLACE PÚBLICO
|--------------------------------------------------------------------------
|
| Página principal de NexoEco.
|
| Puede ser visitada sin iniciar sesión.
|
*/

Route::get(
    '/',
    [MarketplaceController::class, 'index']
)->name('marketplace.index');


/*
|--------------------------------------------------------------------------
| PRODUCTOS PÚBLICOS
|--------------------------------------------------------------------------
|
| El visitante puede revisar la ficha de cualquier producto.
| No se requiere autenticación para consultar el catálogo.
|
*/

Route::get(
    '/productos/{producto}',
    [ProductoController::class, 'show']
)
    ->whereNumber('producto')
    ->name('productos.show');


/*
|--------------------------------------------------------------------------
| TIENDAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get(
    '/tiendas/{tienda}',
    [TiendaController::class, 'show']
)
    ->whereNumber('tienda')
    ->name('tiendas.show');


/*
|--------------------------------------------------------------------------
| LANDING / VENDER EN NEXOECO
|--------------------------------------------------------------------------
|
| Conservamos la antigua landing.
| Más adelante se especializará como página de captación
| para vendedores.
|
*/

Route::get('/vender', function () {
    return view('welcome');
})->name('vender');


/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD COMPRADOR
    |--------------------------------------------------------------------------
    */

    Route::prefix('comprador')
        ->name('comprador.')
        ->group(function () {

            Route::get('/dashboard', function () {
                return view('comprador.dashboard');
            })->name('dashboard');

        });


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD VENDEDOR
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    | Posteriormente agregaremos middleware de autorización
    | específico para vendedores.
    |
    */

    Route::prefix('vendedor')
        ->name('vendedor.')
        ->group(function () {

            Route::get('/dashboard', function () {
                return view('vendedor.dashboard');
            })->name('dashboard');

        });


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ADMINISTRADOR
    |--------------------------------------------------------------------------
    |
    | Posteriormente agregaremos middleware administrativo.
    |
    */

    Route::prefix('administrador')
        ->name('administrador.')
        ->group(function () {

            Route::get(
                '/dashboard',
                [Dashboard_Admin_Controller::class, 'index']
            )->name('dashboard');

        });


    /*
    |--------------------------------------------------------------------------
    | CRUD USUARIOS ADMIN
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | CREAR
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/users',
                [AdminUserController::class, 'store']
            )->name('users.store');


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/users/{user}',
                [AdminUserController::class, 'update']
            )->name('users.update');


            /*
            |--------------------------------------------------------------------------
            | DESACTIVAR / REACTIVAR
            |--------------------------------------------------------------------------
            */

            Route::delete(
                '/users/{user}',
                [AdminUserController::class, 'destroy']
            )->name('users.destroy');

        });


    /*
    |--------------------------------------------------------------------------
    | TIPOS DE USUARIO
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'tipos-usuario',
        TipoUsuarioController::class
    );


    /*
    |--------------------------------------------------------------------------
    | PERFIL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');


    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');


    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | PASSWORD
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/password',
        [PasswordController::class, 'update']
    )->name('password.update');


    /*
    |--------------------------------------------------------------------------
    | EMAIL VERIFICATION
    |--------------------------------------------------------------------------
    */

    Route::get('/email/verify', function () {

        return view('auth.verify-email');

    })->name('verification.notice');


    Route::get(
        '/email/verify/{id}/{hash}',
        function (EmailVerificationRequest $request) {

            $request->fulfill();

            return redirect()
                ->route('comprador.dashboard');

        }
    )
        ->middleware([
            'signed',
            'throttle:6,1',
        ])
        ->name('verification.verify');


    Route::post(
        '/email/verification-notification',
        function (Request $request) {

            $request
                ->user()
                ->sendEmailVerificationNotification();

            return back()->with(
                'status',
                'verification-link-sent'
            );

        }
    )
        ->middleware('throttle:6,1')
        ->name('verification.send');

});


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get(
    '/login',
    [AuthenticatedSessionController::class, 'create']
)
    ->middleware('guest')
    ->name('login');


Route::post(
    '/login',
    [AuthenticatedSessionController::class, 'store']
)
    ->middleware('guest');


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    [AuthenticatedSessionController::class, 'destroy']
)
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| REGISTRO
|--------------------------------------------------------------------------
*/

Route::get(
    '/register',
    [RegisteredUserController::class, 'create']
)
    ->middleware('guest')
    ->name('register');


Route::post(
    '/register',
    [RegisteredUserController::class, 'store']
)
    ->middleware('guest');


/*
|--------------------------------------------------------------------------
| RECUPERAR CONTRASEÑA
|--------------------------------------------------------------------------
*/

Route::get(
    '/forgot-password',
    [PasswordResetLinkController::class, 'create']
)
    ->middleware('guest')
    ->name('password.request');


Route::post(
    '/forgot-password',
    [PasswordResetLinkController::class, 'store']
)
    ->middleware('guest')
    ->name('password.email');


Route::get(
    '/reset-password/{token}',
    function (Request $request, string $token) {

        return view('auth.reset-password', [
            'request' => $request,
            'token' => $token,
        ]);

    }
)
    ->middleware('guest')
    ->name('password.reset');


Route::post(
    '/reset-password',
    [NewPasswordController::class, 'store']
)
    ->middleware('guest')
    ->name('password.store');