<?php

use App\Models\Categoria;
use App\Models\ProductChat;
use App\Models\SellerSubscription;
use App\Models\TiposUsuario;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Tests\Support\BuyerDatabase;

test('all marketplace workspaces render with isolated realistic audit data', function () {
    BuyerDatabase::migrate();
    Http::preventStrayRequests();
    Notification::fake();
    $users = [];
    foreach (['comprador', 'vendedor', 'moderador', 'administrador'] as $role) {
        $type = TiposUsuario::create(['nombre_tipo' => $role]);
        $users[$role] = User::create(['name' => 'Cuenta de prueba '.$role, 'email' => $role.'@example.test', 'password' => 'Audit-test-123!', 'activo' => true, 'email_verified_at' => now()]);
        $users[$role]->tipos_usuario()->attach($type);
    }
    foreach (['sin-verificar' => 'comprador', 'vendedor-nuevo' => 'vendedor'] as $key => $role) {
        $users[$key] = User::create(['name' => 'Cuenta QA '.$key, 'email' => $key.'@example.test', 'password' => 'Audit-test-123!', 'activo' => true, 'email_verified_at' => $key === 'sin-verificar' ? null : now()]);
        $users[$key]->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', $role)->first());
    }
    $shop = $users['vendedor']->tiendas()->create(['giro' => 'Artesanías', 'nombre_tienda' => 'Artesanías de nuestra comunidad', 'descripcion_tienda' => str_repeat('Descripción de la tienda local. ', 10), 'fecha_creacion' => now()]);
    $category = Categoria::firstOrCreate(['nombre_categoria' => 'Cerámica']);
    $product = $shop->productos()->create(['nombre_producto' => 'Cerámica artesanal '.str_repeat('con detalles únicos ', 6), 'descripcion' => str_repeat('Información sobre materiales y entrega. ', 15), 'precio' => 250, 'codigo_producto' => 'AUDIT-001', 'id_categoria' => $category->getKey(), 'fecha_publicacion' => now()]);
    $ticket = $users['comprador']->solicitudesSoporte()->create(['asunto' => 'Consulta de prueba sobre mi cuenta', 'mensaje' => 'Necesito orientación para utilizar la plataforma.']);
    $application = $users['comprador']->solicitudVendedor()->create(['estado' => 'en_revision', 'fecha_solicitud' => now()]);
    $users['comprador']->opiniones()->create(['id_producto' => $product->getKey(), 'calificacion' => 4, 'comentario' => 'Producto artesanal de buena calidad y atención.']);
    SellerSubscription::create(['user_id' => $users['vendedor']->id, 'plan' => 'pro', 'status' => 'authorized', 'amount' => 0, 'paid_until' => now()->addMonth()]);
    $this->actingAs($users['comprador'])->post(route('chat.start', $product))->assertRedirect();
    $chat = ProductChat::firstOrFail();

    $pages = [];
    $add = function ($role, $name, $params = []) use (&$pages) { $pages[] = compact('role', 'name', 'params'); };
    foreach (['marketplace.index', 'vender', 'conocenos', 'login', 'register', 'password.request', 'vendedor.register'] as $name) $add(null, $name);
    $add(null, 'password.reset', ['token' => 'audit-token', 'email' => 'comprador@example.test']);
    foreach (['comprador.dashboard', 'comprador.opiniones', 'comprador.notificaciones', 'chat.index', 'apartados.index', 'profile.edit'] as $name) $add('comprador', $name);
    foreach (['productos', 'tiendas'] as $tipo) $add('comprador', 'comprador.favoritos', compact('tipo'));
    foreach (['preguntas-frecuentes', 'ayuda', 'contacto'] as $seccion) $add('comprador', 'comprador.soporte', compact('seccion'));
    $add('comprador', 'comprador.soporte.show', [$ticket]);
    $add('comprador', 'productos.show', [$product]);
    $add('comprador', 'tiendas.show', [$shop]);
    $add('comprador', 'chat.show', [$chat]);
    foreach (['vendedor.dashboard', 'vendedor.tienda.edit', 'vendedor.productos.index', 'vendedor.productos.create', 'vendedor.mensajes.index', 'vendedor.apartados.index', 'vendedor.suscripcion.index', 'vendedor.herramientas.index', 'vendedor.calendario', 'vendedor.promociones.index', 'vendedor.novedades.index', 'vendedor.estadisticas.index', 'vendedor.notificaciones'] as $name) $add('vendedor', $name);
    $add('vendedor', 'vendedor.productos.edit', [$product]);
    $add('vendedor', 'chat.show', [$chat]);
    foreach (['dashboard', 'publicaciones', 'opiniones', 'reportes', 'vendedores', 'analiticas', 'configuracion', 'ayuda'] as $page) $add('moderador', 'moderador.'.$page);
    $add('moderador', 'moderador.solicitudes.show', [$application]);
    foreach (['administrador.dashboard', 'admin.users.index', 'admin.users.create', 'admin.consultas.index', 'admin.solicitudes.index', 'admin.opiniones', 'profile.edit'] as $name) $add('administrador', $name);
    foreach (['admin.users.show', 'admin.users.edit'] as $name) $add('administrador', $name, [$users['comprador']]);
    $add('administrador', 'admin.consultas.show', [$ticket]);
    $add('administrador', 'admin.solicitudes.show', [$application]);
    $add('administrador', 'admin.productos.show', [$product]);
    $add('administrador', 'admin.tiendas.show', [$shop]);
    foreach (['productos', 'tiendas'] as $tipo) $add('administrador', 'admin.catalogo', compact('tipo'));
    $add('administrador', 'tipos-usuario.index');
    $add('administrador', 'tipos-usuario.create');
    foreach (['tipos-usuario.show', 'tipos-usuario.edit'] as $name) $add('administrador', $name, [TiposUsuario::first()]);
    $add('sin-verificar', 'verification.notice');
    $add('vendedor-nuevo', 'vendedor.tienda.create');
    $add('vendedor-nuevo', 'vendedor.herramientas.index');
    $add('vendedor-nuevo', 'vendedor.suscripcion.index');
    $add('comprador', 'vendedor.register');

    $export = getenv('NEXOECO_AUDIT_EXPORT') === '1';
    $directory = storage_path('framework/audit-pages');
    $manifest = [];
    if ($export) {
        File::ensureDirectoryExists($directory);
        URL::forceRootUrl('http://127.0.0.1:4179');
        Vite::useHotFile(storage_path('framework/audit-no-hot'));
    }
    foreach ($pages as $index => $page) {
        auth()->forgetGuards();
        if ($page['role']) $this->actingAs($users[$page['role']]);
        $url = route($page['name'], $page['params']);
        $response = $this->get($url);
        expect($response->status(), $page['name'].' ('.($page['role'] ?? 'publico').'): '.$response->headers->get('Location'))->toBe(200);
        if ($export) {
            $id = sprintf('%02d', $index).'-'.($page['role'] ?? 'publico').'-'.$page['name'];
            File::put($directory.'/'.$id.'.html', $response->getContent());
            $manifest[] = ['id' => $id, 'route' => $page['name'], 'url' => $url, 'role' => $page['role'], 'view' => $response->original->name()];
        }
    }
    if ($export) File::put($directory.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
});
