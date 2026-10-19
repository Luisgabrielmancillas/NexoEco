<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\TiposUsuario;
use App\Models\User;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    foreach (['comprador', 'administrador', 'vendedor', 'moderador'] as $role) {
        TiposUsuario::create(['nombre_tipo' => $role]);
    }
    $this->admin = User::create(['name' => 'Administradora', 'email' => 'admin@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
    $this->admin->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'administrador')->value('id_tipo_usuario'));
    $this->buyer = User::create(['name' => 'Compradora', 'email' => 'buyer@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
    $this->buyer->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'comprador')->value('id_tipo_usuario'));
    $this->store = Tienda::create(['id_vendedor' => $this->buyer->id, 'nombre_tienda' => 'Tienda para revisar']);
    $category = Categoria::create(['nombre_categoria' => 'Artesanía']);
    $this->product = Producto::create(['id_tienda' => $this->store->getKey(), 'id_categoria' => $category->getKey(), 'codigo_producto' => 'ADMIN-001', 'nombre_producto' => 'Producto para revisar', 'precio' => 150]);
    $this->ticket = $this->buyer->solicitudesSoporte()->create(['asunto' => 'Consulta de prueba', 'mensaje' => 'Mensaje de prueba para revisar la navegación.']);
    $this->application = $this->buyer->solicitudVendedor()->create(['estado' => 'en_revision', 'rfc' => 'LOPM900101AB1', 'fecha_solicitud' => now()]);
    $this->buyer->opiniones()->create(['id_producto' => $this->product->getKey(), 'calificacion' => 5, 'comentario' => 'Reseña real para revisar el enlace administrativo.']);
});

test('all administrative pages and the personal profile use only administrative navigation', function () {
    $urls = [route('administrador.dashboard'), route('admin.users.index'), route('admin.users.create'), route('admin.users.show', $this->buyer), route('admin.users.edit', $this->buyer), route('admin.consultas.index'), route('admin.consultas.show', $this->ticket), route('admin.solicitudes.index'), route('admin.solicitudes.show', $this->application), route('admin.catalogo', 'productos'), route('admin.catalogo', 'tiendas'), route('admin.productos.show', $this->product), route('admin.tiendas.show', $this->store), route('admin.opiniones'), route('profile.edit')];
    foreach ($urls as $url) {
        $this->actingAs($this->admin)->get($url)->assertOk()
            ->assertSee('Navegación administrativa')->assertSee('Inicio de administración')
            ->assertSee('href="'.route('administrador.dashboard').'"', false)
            ->assertDontSee('Navegación del comprador')->assertDontSee('class="market-search"', false)
            ->assertDontSee('data-notification-bell', false)->assertDontSee('data-favorite', false)
            ->assertDontSee('href="'.url('/comprador'), false)
            ->assertDontSee('href="'.url('/productos'), false)
            ->assertDontSee('href="'.url('/tiendas'), false)
            ->assertDontSee('href="'.route('vendedor.register').'"', false);
    }
    $this->get(route('admin.catalogo', 'productos'))->assertSee('href="'.route('admin.productos.show', $this->product).'"', false);
    $this->get(route('admin.opiniones'))->assertSee('href="'.route('admin.productos.show', $this->product).'#opiniones"', false);
});

test('administrator URLs cannot enter buyer or seller dashboards even with multiple roles', function () {
    $this->admin->tipos_usuario()->attach(TiposUsuario::whereIn('nombre_tipo', ['comprador', 'vendedor'])->pluck('id_tipo_usuario'));
    $urls = [route('marketplace.index'), route('comprador.dashboard'), route('comprador.favoritos', 'productos'), route('comprador.opiniones'), route('comprador.notificaciones'), route('comprador.soporte', 'contacto'), route('comprador.soporte.show', $this->ticket), route('vendedor.dashboard'), route('vendedor.register'), route('vendedor.solicitud.create'), route('vender'), route('moderador.dashboard')];
    foreach ($urls as $url) {
        $this->actingAs($this->admin)->get($url)->assertRedirect(route('administrador.dashboard'));
    }
    $this->get(route('productos.show', $this->product))->assertRedirect(route('admin.productos.show', $this->product));
    $this->get(route('tiendas.show', $this->store))->assertRedirect(route('admin.tiendas.show', $this->store));
    $this->postJson(route('comprador.favoritos.store', ['productos', $this->product]))->assertForbidden();
    $this->post(route('comprador.opiniones.store', ['productos', $this->product]), ['calificacion' => 1, 'comentario' => 'Reseña administrativa no permitida'])->assertForbidden();
    $this->post(route('comprador.soporte.store'), ['asunto' => 'Mensaje del administrador', 'mensaje' => 'No debe crearse como consulta del comprador.'])->assertForbidden();
    $this->post(route('comprador.soporte.reply', $this->ticket), ['mensaje' => 'Respuesta desde la ruta equivocada.'])->assertForbidden();
    $this->post(route('vendedor.solicitud.store'))->assertForbidden();
    $this->getJson(route('comprador.notificaciones.count'))->assertForbidden();
    $this->assertDatabaseCount('productos_favoritos', 0);
    $this->assertDatabaseCount('opiniones', 1);
    $this->assertDatabaseCount('solicitudes_soporte', 1);
    $this->assertDatabaseCount('respuestas_soporte', 0);
});

test('administrator login ignores a buyer destination stored before authentication', function () {
    $this->get(route('productos.show', $this->product))->assertRedirect(route('login'));
    $this->post(route('login'), ['email' => $this->admin->email, 'password' => 'clave-segura-123'])
        ->assertRedirect(route('administrador.dashboard'))->assertSessionMissing('url.intended');
    $this->get(route('administrador.dashboard'))->assertOk()->assertSee('Navegación administrativa');
});

test('administrator verification destinations stay in administration', function () {
    $this->actingAs($this->admin)->get(route('verification.notice'))->assertRedirect(route('administrador.dashboard'));
    $this->post(route('verification.send'))->assertRedirect(route('administrador.dashboard'));
    $this->post(route('verification.verify'))->assertRedirect(route('administrador.dashboard'));
    $this->admin->update(['email_verified_at' => null]);
    $this->get(route('comprador.dashboard'))->assertRedirect(route('verification.notice'));
    $this->get(route('administrador.dashboard'))->assertRedirect(route('verification.notice'));
    $this->admin->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', 'vendedor')->value('id_tipo_usuario'));
    $this->get(route('verification.notice'))->assertOk()->assertSee('Navegación administrativa')->assertDontSee('data-notification-bell', false)->assertDontSee('class="market-search"', false);
});

test('administrative catalog details reject buyer seller moderator and anonymous sessions', function () {
    $urls = [route('admin.productos.show', $this->product), route('admin.tiendas.show', $this->store)];
    foreach ($urls as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }
    foreach (['comprador', 'vendedor', 'moderador'] as $role) {
        $this->buyer->tipos_usuario()->sync([TiposUsuario::where('nombre_tipo', $role)->value('id_tipo_usuario')]);
        foreach ($urls as $url) {
            $this->actingAs($this->buyer)->get($url)->assertForbidden();
        }
    }
});
