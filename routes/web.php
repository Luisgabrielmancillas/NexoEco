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
use App\Http\Controllers\SolicitudVendedorController;


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
| Puede visitarse sin iniciar sesión.
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
| Cualquier visitante puede consultar la ficha de un producto.
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
|
| Página pública de cada emprendimiento.
|
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
| Página pública informativa para personas interesadas en vender.
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
    | SOLICITUD PARA VENDER
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    |
    | Tener una solicitud NO significa tener rol vendedor.
    |
    | Durante este proceso el usuario continúa siendo comprador.
    | El rol vendedor solamente será otorgado por un moderador
    | después de aprobar la documentación.
    |
    */

    Route::middleware(['verified'])
        ->prefix('vender')
        ->name('vendedor.solicitud.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | FORMULARIO / ESTADO DE LA SOLICITUD
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/solicitud',
                [SolicitudVendedorController::class, 'create']
            )->name('create');


            /*
            |--------------------------------------------------------------------------
            | ENVIAR SOLICITUD Y DOCUMENTOS
            |--------------------------------------------------------------------------
            |
            | Limitamos intentos para evitar abuso y cargas masivas
            | de archivos.
            |
            */

            Route::post(
                '/solicitud',
                [SolicitudVendedorController::class, 'store']
            )
                ->middleware('throttle:5,1')
                ->name('store');

        });


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD COMPRADOR
    |--------------------------------------------------------------------------
    |
    | Posteriormente agregaremos autorización formal por rol.
    |
    */

    Route::prefix('comprador')
        ->name('comprador.')
        ->group(function () {

            Route::get(
                '/dashboard',
                function () {

                    return view(
                        'comprador.dashboard'
                    );

                }
            )->name('dashboard');

        });


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD VENDEDOR
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    |
    | Este dashboard NO debe utilizarse para solicitudes pendientes.
    |
    | Posteriormente se protegerá con middleware de rol vendedor:
    |
    | rol:vendedor
    |
    */

    Route::prefix('vendedor')
        ->name('vendedor.')
        ->group(function () {

            Route::get(
                '/dashboard',
                function () {

                    return view(
                        'vendedor.dashboard'
                    );

                }
            )->name('dashboard');

        });


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ADMINISTRADOR
    |--------------------------------------------------------------------------
    |
    | Posteriormente agregaremos middleware específico para
    | administración.
    |
    */

    Route::prefix('administrador')
        ->name('administrador.')
        ->group(function () {

            Route::get(
                '/dashboard',
                [
                    Dashboard_Admin_Controller::class,
                    'index'
                ]
            )->name('dashboard');

        });


    /*
    |--------------------------------------------------------------------------
    | CRUD USUARIOS ADMIN
    |--------------------------------------------------------------------------
    |
    | Posteriormente estas rutas deben recibir middleware administrativo.
    |
    */

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | CREAR USUARIO
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/users',
                [
                    AdminUserController::class,
                    'store'
                ]
            )->name('users.store');


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR USUARIO
            |--------------------------------------------------------------------------
            */

            Route::put(
                '/users/{user}',
                [
                    AdminUserController::class,
                    'update'
                ]
            )
                ->whereNumber('user')
                ->name('users.update');


            /*
            |--------------------------------------------------------------------------
            | DESACTIVAR / REACTIVAR USUARIO
            |--------------------------------------------------------------------------
            */

            Route::delete(
                '/users/{user}',
                [
                    AdminUserController::class,
                    'destroy'
                ]
            )
                ->whereNumber('user')
                ->name('users.destroy');

        });


    /*
    |--------------------------------------------------------------------------
    | TIPOS DE USUARIO
    |--------------------------------------------------------------------------
    |
    | Posteriormente debe quedar disponible únicamente para los
    | roles administrativos correspondientes.
    |
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
        [
            ProfileController::class,
            'edit'
        ]
    )->name('profile.edit');


    Route::patch(
        '/profile',
        [
            ProfileController::class,
            'update'
        ]
    )->name('profile.update');


    Route::delete(
        '/profile',
        [
            ProfileController::class,
            'destroy'
        ]
    )->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | PASSWORD
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/password',
        [
            PasswordController::class,
            'update'
        ]
    )->name('password.update');


    /*
    |--------------------------------------------------------------------------
    | VERIFICACIÓN DE EMAIL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/email/verify',
        function () {

            return view(
                'auth.verify-email'
            );

        }
    )->name('verification.notice');


    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR VERIFICACIÓN
    |--------------------------------------------------------------------------
    |
    | Si el usuario tiene una solicitud para vender y todavía no
    | cuenta con el rol vendedor, después de verificar el email
    | lo enviamos al proceso de solicitud.
    |
    */

    Route::get(
        '/email/verify/{id}/{hash}',
        function (
            EmailVerificationRequest $request
        ) {

            /*
            |--------------------------------------------------------------------------
            | VERIFICAR EMAIL
            |--------------------------------------------------------------------------
            */

            $request->fulfill();


            /*
            |--------------------------------------------------------------------------
            | RECARGAR USUARIO
            |--------------------------------------------------------------------------
            */

            $user = $request
                ->user()
                ->fresh();


            /*
            |--------------------------------------------------------------------------
            | SOLICITUD DE VENDEDOR
            |--------------------------------------------------------------------------
            |
            | Si seleccionó "Quiero vender" durante el registro,
            | RegisteredUserController habrá creado una solicitud.
            |
            | Todavía NO debe tener rol vendedor.
            |
            */

            $tieneSolicitudVendedor = $user
                ->solicitudVendedor()
                ->exists();


            $yaEsVendedor = $user
                ->tieneTipo('vendedor');


            if (
                $tieneSolicitudVendedor
                && !$yaEsVendedor
            ) {
                return redirect()
                    ->route(
                        'vendedor.solicitud.create'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | COMPRADOR NORMAL
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'comprador.dashboard'
                );

        }
    )
        ->middleware([
            'signed',
            'throttle:6,1',
        ])
        ->name('verification.verify');


    /*
    |--------------------------------------------------------------------------
    | REENVIAR VERIFICACIÓN
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/email/verification-notification',
        function (Request $request) {

            /*
            |--------------------------------------------------------------------------
            | EVITAR REENVÍO INNECESARIO
            |--------------------------------------------------------------------------
            */

            if (
                $request
                    ->user()
                    ->hasVerifiedEmail()
            ) {
                return redirect()
                    ->route(
                        'marketplace.index'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | ENVIAR CORREO
            |--------------------------------------------------------------------------
            */

            $request
                ->user()
                ->sendEmailVerificationNotification();


            return back()->with(
                'status',
                'verification-link-sent'
            );

        }
    )
        ->middleware(
            'throttle:6,1'
        )
        ->name(
            'verification.send'
        );

});


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get(
    '/login',
    [
        AuthenticatedSessionController::class,
        'create'
    ]
)
    ->middleware('guest')
    ->name('login');


Route::post(
    '/login',
    [
        AuthenticatedSessionController::class,
        'store'
    ]
)
    ->middleware([
        'guest',
        'throttle:10,1',
    ]);


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    [
        AuthenticatedSessionController::class,
        'destroy'
    ]
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
    [
        RegisteredUserController::class,
        'create'
    ]
)
    ->middleware('guest')
    ->name('register');


Route::post(
    '/register',
    [
        RegisteredUserController::class,
        'store'
    ]
)
    ->middleware([
        'guest',
        'throttle:5,1',
    ]);


/*
|--------------------------------------------------------------------------
| RECUPERAR CONTRASEÑA
|--------------------------------------------------------------------------
*/

Route::get(
    '/forgot-password',
    [
        PasswordResetLinkController::class,
        'create'
    ]
)
    ->middleware('guest')
    ->name('password.request');


Route::post(
    '/forgot-password',
    [
        PasswordResetLinkController::class,
        'store'
    ]
)
    ->middleware([
        'guest',
        'throttle:5,1',
    ])
    ->name('password.email');


/*
|--------------------------------------------------------------------------
| FORMULARIO NUEVA CONTRASEÑA
|--------------------------------------------------------------------------
*/

Route::get(
    '/reset-password/{token}',
    function (
        Request $request,
        string $token
    ) {

        return view(
            'auth.reset-password',
            [
                'request' => $request,
                'token' => $token,
            ]
        );

    }
)
    ->middleware('guest')
    ->name('password.reset');


/*
|--------------------------------------------------------------------------
| GUARDAR NUEVA CONTRASEÑA
|--------------------------------------------------------------------------
*/

Route::post(
    '/reset-password',
    [
        NewPasswordController::class,
        'store'
    ]
)
    ->middleware([
        'guest',
        'throttle:5,1',
    ])
    ->name('password.store');