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
            Route::get('/suscripcion', [\App\Http\Controllers\SellerSubscriptionController::class, 'index'])->name('suscripcion.index');
            Route::post('/suscripcion/gratis', [\App\Http\Controllers\SellerSubscriptionController::class, 'activarPlanGratuito'])->middleware('throttle:5,1,subscription-checkout')->name('suscripcion.gratis');
            Route::get('/herramientas', [\App\Http\Controllers\SellerToolsController::class, 'index'])->name('herramientas.index');
            Route::post('/herramientas/respuestas', [\App\Http\Controllers\SellerToolsController::class, 'saveReply'])->middleware('throttle:30,1,seller-replies')->name('herramientas.respuestas.store');
            Route::put('/herramientas/respuestas/{reply}', [\App\Http\Controllers\SellerToolsController::class, 'saveReply'])->whereNumber('reply')->middleware('throttle:30,1,seller-replies')->name('herramientas.respuestas.update');
            Route::delete('/herramientas/respuestas/{reply}', [\App\Http\Controllers\SellerToolsController::class, 'deleteReply'])->whereNumber('reply')->name('herramientas.respuestas.destroy');
            Route::put('/herramientas/automatizaciones', [\App\Http\Controllers\SellerToolsController::class, 'automation'])->middleware('throttle:20,1,seller-automations')->name('herramientas.automatizaciones');
            Route::put('/herramientas/personalizacion', [\App\Http\Controllers\SellerToolsController::class, 'design'])->middleware('throttle:20,1,seller-design')->name('herramientas.personalizacion');
            Route::post('/herramientas/fijados/{producto}', [\App\Http\Controllers\SellerToolsController::class, 'pin'])->whereNumber('producto')->middleware('throttle:30,1,seller-pins')->name('herramientas.fijados');
            Route::put('/mensajes/{chat}/negociacion', [\App\Http\Controllers\SellerToolsController::class, 'negotiation'])->whereNumber('chat')->middleware('throttle:30,1,seller-negotiations')->name('mensajes.negociacion');
            Route::put('/apartados/{apartado}/seguimiento', [\App\Http\Controllers\SellerToolsController::class, 'fulfillment'])->middleware('throttle:30,1,seller-fulfillment')->name('apartados.seguimiento');
            Route::get('/calendario', [\App\Http\Controllers\SellerToolsController::class, 'calendar'])->name('calendario');
            Route::get('/promociones', [\App\Http\Controllers\SellerOfferController::class, 'index'])->name('promociones.index');
            Route::post('/promociones', [\App\Http\Controllers\SellerOfferController::class, 'save'])->middleware('throttle:20,1,seller-offers')->name('promociones.store');
            Route::put('/promociones/{offer}', [\App\Http\Controllers\SellerOfferController::class, 'save'])->whereNumber('offer')->middleware('throttle:20,1,seller-offers')->name('promociones.update');
            Route::delete('/promociones/{offer}', [\App\Http\Controllers\SellerOfferController::class, 'disable'])->whereNumber('offer')->name('promociones.destroy');
            Route::get('/estadisticas', [\App\Http\Controllers\SellerAnalyticsController::class, 'index'])->name('estadisticas.index');
            Route::get('/estadisticas/reporte', [\App\Http\Controllers\SellerAnalyticsController::class, 'export'])->middleware('throttle:5,1,seller-reports')->name('estadisticas.export');
            Route::get('/novedades', [\App\Http\Controllers\StoreAudienceController::class, 'index'])->name('novedades.index');
            Route::post('/novedades', [\App\Http\Controllers\StoreAudienceController::class, 'save'])->middleware('throttle:5,1,seller-updates')->name('novedades.store');
            Route::delete('/novedades/{update}', [\App\Http\Controllers\StoreAudienceController::class, 'destroy'])->whereNumber('update')->name('novedades.destroy');
            Route::post('/suscripcion/contratar', [\App\Http\Controllers\SellerSubscriptionController::class, 'checkout'])->middleware('throttle:5,1,subscription-checkout')->name('suscripcion.checkout');
            Route::get('/suscripcion/{subscription}/retorno', [\App\Http\Controllers\SellerSubscriptionController::class, 'returned'])->middleware('throttle:10,1,subscription-return')->name('suscripcion.return');
            Route::post('/suscripcion/{subscription}/actualizar', [\App\Http\Controllers\SellerSubscriptionController::class, 'sync'])->middleware('throttle:10,1,subscription-sync')->name('suscripcion.sync');
            Route::post('/suscripcion/{subscription}/cancelar', [\App\Http\Controllers\SellerSubscriptionController::class, 'cancel'])->middleware('throttle:5,1,subscription-cancel')->name('suscripcion.cancel');
            Route::post('/suscripcion/productos/{producto}', [\App\Http\Controllers\SellerSubscriptionController::class, 'promote'])->whereNumber('producto')->middleware('throttle:30,1,subscription-products')->name('suscripcion.promote');
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