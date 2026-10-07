<?php

use App\Http\Controllers\AdminCatalogController;
use App\Http\Controllers\AdminSellerApplicationController;
// Controllers
use App\Http\Controllers\AdminSupportController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\NexoEmailVerificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SellerRegistrationController;
use App\Http\Controllers\BuyerNotificationController;
use App\Http\Controllers\BuyerSupportController;
use App\Http\Controllers\Dashboard_Admin_Controller;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OpinionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\SolicitudVendedorController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\TipoUsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/media/tiendas/{tienda}/{archivo}', [\App\Http\Controllers\ShopMediaController::class, 'show'])
    ->whereNumber('tienda')->name('tiendas.media');

Route::post('/ubicacion', [\App\Http\Controllers\BuyerLocationController::class, 'update'])
    ->middleware('throttle:30,1')->name('marketplace.ubicacion.update');

/*
|--------------------------------------------------------------------------
| DETALLE DE PRODUCTOS
|--------------------------------------------------------------------------
|
| El catálogo es público; abrir una ficha requiere iniciar sesión.
|
*/

Route::get(
    '/productos/{producto}',
    [ProductoController::class, 'show']
)
    ->middleware('auth')
    ->whereNumber('producto')
    ->name('productos.show');

/*
|--------------------------------------------------------------------------
| DETALLE DE TIENDAS
|--------------------------------------------------------------------------
|
| Abrir el catálogo de una tienda requiere iniciar sesión.
|
*/

Route::get(
    '/tiendas/{tienda}',
    [TiendaController::class, 'show']
)
    ->middleware('auth')
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

    Route::prefix('moderador')->name('moderador.')->middleware(['verified', 'moderator'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\ModeratorController::class, 'dashboard'])->name('dashboard');
        Route::get('/publicaciones', [\App\Http\Controllers\ModeratorController::class, 'publications'])->name('publicaciones');
        Route::get('/opiniones', [\App\Http\Controllers\ModeratorController::class, 'opinions'])->name('opiniones');
        Route::delete('/contenido/{tipo}/{id}', [\App\Http\Controllers\ModeratorController::class, 'destroy'])->whereIn('tipo', ['productos', 'opiniones'])->whereNumber('id')->middleware('throttle:30,1')->name('contenido.destroy');
        Route::get('/reportes', [\App\Http\Controllers\ModeratorController::class, 'reports'])->name('reportes');
        Route::post('/reportes/{reporte}/resolver', [\App\Http\Controllers\ModeratorController::class, 'resolve'])->whereNumber('reporte')->middleware('throttle:30,1')->name('reportes.resolve');
        Route::get('/vendedores', [\App\Http\Controllers\ModeratorController::class, 'sellers'])->name('vendedores');
        Route::get('/vendedores/{solicitud}', [\App\Http\Controllers\ModeratorController::class, 'application'])->whereNumber('solicitud')->name('solicitudes.show');
        Route::get('/vendedores/{solicitud}/documentos/{documento}', [AdminSellerApplicationController::class, 'document'])->whereNumber(['solicitud', 'documento'])->name('solicitudes.document');
        Route::get('/vendedores/{solicitud}/documentos/{documento}/preview', [AdminSellerApplicationController::class, 'preview'])->whereNumber(['solicitud', 'documento'])->name('solicitudes.preview');
        Route::post('/vendedores/{solicitud}/revisar', [AdminSellerApplicationController::class, 'review'])->whereNumber('solicitud')->middleware('throttle:20,1')->name('solicitudes.review');
        Route::get('/analiticas', [\App\Http\Controllers\ModeratorController::class, 'analytics'])->name('analiticas');
        Route::get('/configuracion', [\App\Http\Controllers\ModeratorController::class, 'settings'])->name('configuracion');
        Route::get('/ayuda', [\App\Http\Controllers\ModeratorController::class, 'help'])->name('ayuda');
    });

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

    Route::prefix('vender')
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
                [MarketplaceController::class, 'index']
            )->middleware('verified')->name('dashboard');

            Route::middleware('verified')->group(function () {
                Route::post('/reportes/{tipo}/{id}', [\App\Http\Controllers\ContentReportController::class, 'store'])->whereIn('tipo', ['productos', 'opiniones'])->whereNumber('id')->middleware('throttle:5,1')->name('reportes.store');
                Route::get('/favoritos/{tipo}', [FavoritoController::class, 'index'])
                    ->whereIn('tipo', ['productos', 'tiendas'])->name('favoritos');
                Route::post('/favoritos/{tipo}/{id}', [FavoritoController::class, 'store'])
                    ->whereIn('tipo', ['productos', 'tiendas'])->whereNumber('id')->middleware('throttle:60,1')->name('favoritos.store');
                Route::delete('/favoritos/{tipo}/{id}', [FavoritoController::class, 'destroy'])
                    ->whereIn('tipo', ['productos', 'tiendas'])->whereNumber('id')->name('favoritos.destroy');
                Route::get('/opiniones', [OpinionController::class, 'index'])->name('opiniones');
                Route::post('/opiniones/{tipo}/{id}', [OpinionController::class, 'store'])
                    ->whereIn('tipo', ['productos', 'tiendas'])->whereNumber('id')->middleware('throttle:10,1')->name('opiniones.store');
                Route::delete('/opiniones/{opinion}', [OpinionController::class, 'destroy'])->whereNumber('opinion')->name('opiniones.destroy');
                Route::get('/notificaciones', [BuyerNotificationController::class, 'index'])->name('notificaciones');
                Route::get('/notificaciones/conteo', [BuyerNotificationController::class, 'count'])->name('notificaciones.count');
                Route::post('/notificaciones/leer-todas', [BuyerNotificationController::class, 'readAll'])->name('notificaciones.readAll');
                Route::post('/notificaciones/{notification}/leer', [BuyerNotificationController::class, 'read'])->whereUuid('notification')->name('notificaciones.read');
                Route::get('/soporte/{seccion}', [BuyerSupportController::class, 'show'])
                    ->whereIn('seccion', ['preguntas-frecuentes', 'ayuda', 'contacto'])->name('soporte');
                Route::post('/soporte/contacto', [BuyerSupportController::class, 'store'])->middleware('throttle:5,1')->name('soporte.store');
                Route::get('/soporte/consultas/{solicitud}', [BuyerSupportController::class, 'thread'])->whereNumber('solicitud')->name('soporte.show');
                Route::post('/soporte/consultas/{solicitud}', [BuyerSupportController::class, 'reply'])->whereNumber('solicitud')->middleware('throttle:10,1')->name('soporte.reply');
            });

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
    | Requiere correo verificado y rol vendedor habilitado.
    |
    */

    Route::prefix('vendedor')
        ->name('vendedor.')
        ->middleware(['verified', 'seller'])
        ->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\SellerStoreController::class, 'dashboard'])->name('dashboard');
            Route::get('/tienda/crear', [\App\Http\Controllers\SellerStoreController::class, 'create'])->name('tienda.create');
            Route::post('/tienda', [\App\Http\Controllers\SellerStoreController::class, 'store'])->name('tienda.store');
            Route::get('/tienda/editar', [\App\Http\Controllers\SellerStoreController::class, 'edit'])->name('tienda.edit');
            Route::put('/tienda', [\App\Http\Controllers\SellerStoreController::class, 'update'])->name('tienda.update');
            Route::get('/productos', [\App\Http\Controllers\SellerProductController::class, 'index'])->name('productos.index');
            Route::get('/productos/crear', [\App\Http\Controllers\SellerProductController::class, 'create'])->name('productos.create');
            Route::post('/productos', [\App\Http\Controllers\SellerProductController::class, 'store'])->name('productos.store');
            Route::get('/productos/{producto}/editar', [\App\Http\Controllers\SellerProductController::class, 'edit'])->whereNumber('producto')->name('productos.edit');
            Route::put('/productos/{producto}', [\App\Http\Controllers\SellerProductController::class, 'update'])->whereNumber('producto')->name('productos.update');
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
        ->middleware(['verified', 'administrator'])
        ->group(function () {

            Route::get(
                '/dashboard',
                [
                    Dashboard_Admin_Controller::class,
                    'index',
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
        ->middleware(['verified', 'administrator'])
        ->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->whereNumber('user')->name('users.edit');
            Route::get('/users/{user}', [AdminUserController::class, 'show'])->whereNumber('user')->name('users.show');
            Route::get('/consultas', [AdminSupportController::class, 'index'])->name('consultas.index');
            Route::get('/consultas/{solicitud}', [AdminSupportController::class, 'show'])->whereNumber('solicitud')->name('consultas.show');
            Route::post('/consultas/{solicitud}/responder', [AdminSupportController::class, 'reply'])->whereNumber('solicitud')->middleware('throttle:30,1')->name('consultas.reply');
            Route::get('/catalogo/{tipo}', [AdminCatalogController::class, 'index'])->whereIn('tipo', ['productos', 'tiendas'])->name('catalogo');
            Route::get('/productos/{producto}', [AdminCatalogController::class, 'product'])->whereNumber('producto')->name('productos.show');
            Route::get('/tiendas/{tienda}', [AdminCatalogController::class, 'store'])->whereNumber('tienda')->name('tiendas.show');
            Route::get('/opiniones', [AdminCatalogController::class, 'reviews'])->name('opiniones');
            Route::get('/solicitudes-vendedor', [AdminSellerApplicationController::class, 'index'])->name('solicitudes.index');
            Route::get('/solicitudes-vendedor/{solicitud}', [AdminSellerApplicationController::class, 'show'])->whereNumber('solicitud')->name('solicitudes.show');
            Route::get('/solicitudes-vendedor/{solicitud}/documentos/{documento}', [AdminSellerApplicationController::class, 'document'])->whereNumber(['solicitud', 'documento'])->name('solicitudes.document');
            Route::get('/solicitudes-vendedor/{solicitud}/documentos/{documento}/preview', [AdminSellerApplicationController::class, 'preview'])->whereNumber(['solicitud', 'documento'])->name('solicitudes.preview');
            Route::post('/solicitudes-vendedor/{solicitud}/revisar', [AdminSellerApplicationController::class, 'review'])->whereNumber('solicitud')->middleware('throttle:20,1')->name('solicitudes.review');

            /*
            |--------------------------------------------------------------------------
            | CREAR USUARIO
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/users',
                [
                    AdminUserController::class,
                    'store',
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
                    'update',
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
                    'destroy',
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
    )->middleware(['verified', 'administrator']);

    /*
    |--------------------------------------------------------------------------
    | PERFIL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [
            ProfileController::class,
            'edit',
        ]
    )->name('profile.edit');

    Route::post('/profile/photo', [ProfilePhotoController::class, 'store'])->middleware('throttle:10,1')->name('profile.photo.store');
    Route::delete('/profile/photo', [ProfilePhotoController::class, 'destroy'])->name('profile.photo.destroy');
    Route::get('/usuarios/{user}/foto', [ProfilePhotoController::class, 'show'])->whereNumber('user')->name('profile.photo.show');

    Route::patch(
        '/profile',
        [
            ProfileController::class,
            'update',
        ]
    )->name('profile.update');

    Route::delete(
        '/profile',
        [
            ProfileController::class,
            'destroy',
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
            'update',
        ]
    )->name('password.update');

    /*
    |--------------------------------------------------------------------------
    | VERIFICACIÓN DE EMAIL
    |--------------------------------------------------------------------------
    */

    Route::get('/email/verify', [NexoEmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::post('/email/verify', [NexoEmailVerificationController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('verification.verify');

    Route::post('/email/verification-notification', [NexoEmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

});

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/vendedor/registro', [SellerRegistrationController::class, 'create'])->name('vendedor.register');
Route::post('/vendedor/registro', [SellerRegistrationController::class, 'store'])
    ->middleware(['guest', 'throttle:5,1']);

Route::get(
    '/login',
    [
        AuthenticatedSessionController::class,
        'create',
    ]
)
    ->middleware('guest')
    ->name('login');

Route::post(
    '/login',
    [
        AuthenticatedSessionController::class,
        'store',
    ]
)
    ->middleware([
        'guest',
        'throttle:10,1',
    ])->name('login.store');

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    [
        AuthenticatedSessionController::class,
        'destroy',
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
        'create',
    ]
)
    ->middleware('guest')
    ->name('register');

Route::post(
    '/register',
    [
        RegisteredUserController::class,
        'store',
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
        'create',
    ]
)
    ->middleware('guest')
    ->name('password.request');

Route::post(
    '/forgot-password',
    [
        PasswordResetLinkController::class,
        'store',
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
        'store',
    ]
)
    ->middleware([
        'guest',
        'throttle:5,1',
    ])
    ->name('password.store');
