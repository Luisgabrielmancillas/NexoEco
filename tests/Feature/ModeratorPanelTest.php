<?php

use App\Models\Categoria;
use App\Models\RegistroActividad;
use App\Models\ReporteContenido;
use App\Models\TiposUsuario;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuyerDatabase;

beforeEach(function () {
    BuyerDatabase::migrate();
    Storage::fake('local');
    foreach (['comprador', 'vendedor', 'moderador', 'administrador'] as $role) {
        TiposUsuario::create(['nombre_tipo' => $role]);
    }
    foreach (['moderator' => 'moderador', 'buyer' => 'comprador', 'seller' => 'vendedor', 'admin' => 'administrador'] as $property => $role) {
        $this->{$property} = User::create(['name' => 'Cuenta '.$role, 'email' => $role.'@example.test', 'password' => 'clave-segura-123', 'email_verified_at' => now(), 'activo' => true]);
        $this->{$property}->tipos_usuario()->attach(TiposUsuario::where('nombre_tipo', $role)->value('id_tipo_usuario'));
    }
    $this->shop = $this->seller->tiendas()->create(['nombre_tienda' => 'Tienda local para moderar', 'fecha_creacion' => now()]);
    $this->category = Categoria::create(['nombre_categoria' => 'Artesanías']);
    $this->product = $this->shop->productos()->create(['nombre_producto' => 'Publicación para revisar', 'descripcion' => 'Producto artesanal de nuestra comunidad.', 'codigo_producto' => 'MOD-TEST', 'precio' => 200, 'id_categoria' => $this->category->getKey(), 'fecha_publicacion' => now()]);
    $this->product->forceFill(['estado_moderacion' => 'pendiente'])->save();
    $this->opinion = $this->buyer->opiniones()->create(['id_tienda' => $this->shop->getKey(), 'calificacion' => 4, 'comentario' => 'Opinión pendiente del comprador']);
    $this->opinion->forceFill(['estado_moderacion' => 'pendiente'])->save();
    $this->application = $this->buyer->solicitudVendedor()->create(['estado' => 'en_revision', 'fecha_solicitud' => now()]);
});

test('moderator screens use their own workspace with real counts and protected navigation', function () {
    $this->actingAs($this->moderator);
    foreach (['dashboard', 'publicaciones', 'opiniones', 'vendedores', 'reportes', 'analiticas', 'configuracion', 'ayuda'] as $page) {
        $this->get(route('moderador.'.$page))->assertOk()->assertSee('mod-sidebar', false)->assertDontSee('buyer-nav', false)->assertDontSee('market-location-button', false)->assertDontSee('admin-panel-nav', false);
    }
    $response = $this->get(route('moderador.dashboard'))->assertViewIs('moderador.dashboard')->assertSee('Casos por atender')->assertSee('Acciones recientes')->assertDontSee('mod-preview', false)->assertDontSee('data-mod-delete', false);
    expect($response->viewData('counts'))->toBe(['publicaciones' => 1, 'opiniones' => 1, 'vendedores' => 1, 'reportes' => 0]);
    expect(RegistroActividad::count())->toBe(0);
    $this->get(route('moderador.publicaciones'))->assertViewIs('moderador.publicaciones')->assertSee('Publicación para revisar')->assertSee('Tienda local para moderar')->assertSee('data-mod-delete', false)->assertDontSee('Casos por atender')->assertDontSee('mod-stats', false);
    $this->get(route('moderador.publicaciones', ['q' => 'No existe']))->assertViewHas('productos', fn ($items) => $items->isEmpty());
    $this->get(route('admin.users.index'))->assertForbidden();
    $this->post(route('admin.users.store'), ['rol' => 'administrador'])->assertForbidden();
});

test('summary shows open cases and important moderation activity without duplicating the product manager', function () {
    $open = ReporteContenido::create(['id_usuario' => $this->buyer->id, 'tipo' => 'productos', 'id_contenido' => $this->product->getKey(), 'categoria' => 'spam', 'motivo' => 'Reporte abierto que necesita seguimiento.']);
    $closed = ReporteContenido::create(['id_usuario' => $this->buyer->id, 'tipo' => 'opiniones', 'id_contenido' => $this->opinion->getKey(), 'categoria' => 'otro', 'motivo' => 'Reporte cerrado que no debe aparecer en pendientes.']);
    $closed->forceFill(['estado' => 'resuelto', 'fecha_resolucion' => now()])->save();
    $this->seller->solicitudVendedor()->create(['estado' => 'aprobada', 'fecha_solicitud' => now()]);
    foreach (['moderador.publicaciones', 'admin.users.store'] as $route) {
        RegistroActividad::create(['nombre_actor' => $this->admin->name, 'roles_actor' => ['administrador'], 'accion' => 'Acción ajena a moderación', 'descripcion' => 'Esta actividad no pertenece al resumen del moderador.', 'ruta' => $route, 'metodo' => 'GET', 'registrada_en' => now()]);
    }
    $this->actingAs($this->moderator)->delete(route('moderador.contenido.destroy', ['productos', $this->product->getKey()]), ['version' => 1, 'motivo' => 'Esta publicación contiene información inapropiada.'])->assertSessionHasNoErrors();
    $response = $this->get(route('moderador.dashboard'))->assertOk()->assertViewIs('moderador.dashboard')->assertSee($open->motivo)->assertDontSee($closed->motivo)
        ->assertSee('Eliminó una publicación')->assertSee($this->moderator->name)->assertSee(now()->format('d/m/Y H:i:s'))->assertDontSee('Acción ajena a moderación')->assertDontSee('data-mod-delete', false);
    expect($response->viewData('recentReports')->modelKeys())->toBe([$open->id])
        ->and($response->viewData('pendingSellers')->modelKeys())->toBe([$this->application->getKey()])
        ->and($response->viewData('recentActivity')->count())->toBe(1)
        ->and($response->viewData('resolvedToday'))->toBe(1)
        ->and($response->viewData('deletedToday'))->toBe(1);
});

test('buyers sellers and guests cannot use moderation routes or private documents', function () {
    $this->get(route('moderador.dashboard'))->assertRedirect(route('login'));
    foreach ([$this->buyer, $this->seller] as $user) {
        $this->actingAs($user)->get(route('moderador.dashboard'))->assertForbidden();
        $this->get(route('moderador.solicitudes.show', $this->application))->assertForbidden();
        $this->delete(route('moderador.contenido.destroy', ['productos', $this->product->getKey()]), ['motivo' => 'Contenido inapropiado', 'version' => 1])->assertForbidden();
    }
    $this->actingAs($this->admin)->get(route('moderador.dashboard'))->assertRedirect(route('administrador.dashboard'));
    $this->actingAs($this->moderator);
    $this->moderator->update(['activo' => false]);
    $this->getJson(route('moderador.dashboard'))->assertUnauthorized();
    expect($this->product->fresh()->estado_moderacion)->toBe('pendiente');
});

test('moderator login ignores buyer destinations and even combined roles keep the moderation workspace', function () {
    $this->moderator->tipos_usuario()->attach(TiposUsuario::whereIn('nombre_tipo', ['comprador', 'vendedor'])->pluck('id_tipo_usuario'));
    $this->withSession(['url.intended' => route('comprador.dashboard')])->post(route('login'), ['email' => $this->moderator->email, 'password' => 'clave-segura-123'])->assertRedirect(route('moderador.dashboard'));
    foreach (['marketplace.index', 'comprador.dashboard', 'vendedor.dashboard'] as $route) {
        $this->get(route($route))->assertRedirect(route('moderador.dashboard'));
    }
    $this->get(route('productos.show', $this->product))->assertRedirect(route('moderador.dashboard'));
    $this->get(route('profile.edit'))->assertRedirect(route('moderador.configuracion'));
    $this->postJson(route('marketplace.ubicacion.update'), ['ciudad' => 'Manzanillo', 'estado' => 'Colima'])->assertForbidden();
    $this->post(route('comprador.opiniones.store', ['tiendas', $this->shop->getKey()]), ['comentario' => 'No permitido', 'calificacion' => 5])->assertForbidden();
});

test('unverified moderators must verify their email before accessing their panel', function () {
    $this->moderator->update(['email_verified_at' => null]);
    $this->actingAs($this->moderator)->get(route('moderador.dashboard'))->assertRedirect(route('verification.notice'));
});

test('products and comments are already public regardless of legacy review states', function () {
    foreach (['pendiente', 'rechazada'] as $state) {
        $this->product->forceFill(['estado_moderacion' => $state])->save();
        $this->opinion->forceFill(['estado_moderacion' => $state])->save();
        $this->actingAs($this->buyer)->get(route('comprador.dashboard'))->assertSee('Publicación para revisar');
        $this->get(route('productos.show', $this->product))->assertOk();
        $this->actingAs($this->seller)->get(route('tiendas.show', $this->shop))->assertSee('Opinión pendiente del comprador')->assertSee('4.0 de 5');
    }
    $this->actingAs($this->moderator)->get(route('moderador.publicaciones'))->assertDontSee('Aprobar y publicar')->assertDontSee('Rechazar y ocultar');
    $this->post('/moderador/contenido/productos/'.$this->product->getKey().'/revisar', ['estado' => 'rechazada', 'version' => 1])->assertNotFound();
});

test('moderator deletion requires a reason and removes a product from all buyer surfaces', function () {
    $this->buyer->productosFavoritos()->attach($this->product);
    $this->buyer->opiniones()->create(['id_producto' => $this->product->getKey(), 'calificacion' => 5, 'comentario' => 'Opinión de un producto eliminado']);
    $route = route('moderador.contenido.destroy', ['productos', $this->product->getKey()]);
    $this->actingAs($this->moderator)->deleteJson($route, ['version' => 1, 'motivo' => 'Corto'])->assertUnprocessable()->assertJsonValidationErrors('motivo');
    $this->deleteJson($route, ['version' => 99, 'motivo' => 'Esta versión ya no corresponde al contenido.'])->assertUnprocessable()->assertJsonValidationErrors('version');
    $this->delete($route, ['version' => 1, 'motivo' => 'La publicación incluye contenido inapropiado.'])->assertRedirect(route('moderador.publicaciones'))->assertSessionHasNoErrors();
    $this->assertSoftDeleted('productos', ['id_producto' => $this->product->getKey()]);
    expect($this->seller->notifications()->count())->toBe(1)->and(RegistroActividad::first()->accion)->toBe('Eliminó una publicación');
    $this->get(route('moderador.publicaciones', ['estado' => 'eliminada']))->assertSee('Publicación para revisar')->assertDontSee('data-mod-delete', false);
    $this->actingAs($this->buyer)->get(route('comprador.dashboard'))->assertDontSee('Publicación para revisar');
    $this->get(route('productos.show', $this->product))->assertNotFound();
    $this->get(route('tiendas.show', $this->shop))->assertDontSee('Publicación para revisar');
    $this->get(route('comprador.favoritos', 'productos'))->assertDontSee('Publicación para revisar');
    $this->post(route('comprador.favoritos.store', ['productos', $this->product->getKey()]))->assertNotFound();
    $this->get(route('comprador.opiniones'))->assertOk()->assertSee('Este producto fue eliminado del marketplace.');
    $this->actingAs($this->seller)->put(route('vendedor.productos.update', $this->product), [])->assertNotFound();
});

test('deleting an inappropriate opinion removes its comment and rating and notifies the author', function () {
    $this->actingAs($this->seller)->get(route('tiendas.show', $this->shop))->assertSee('Opinión pendiente del comprador')->assertSee('4.0 de 5');
    $route = route('moderador.contenido.destroy', ['opiniones', $this->opinion->getKey()]);
    $this->actingAs($this->moderator)->delete($route, ['motivo' => 'El comentario contiene información personal.', 'version' => 1])->assertSessionHasNoErrors();
    $this->actingAs($this->seller)->get(route('tiendas.show', $this->shop))->assertDontSee('Opinión pendiente del comprador')->assertDontSee('4.0 de 5');
    expect($this->buyer->notifications()->count())->toBe(1);
    $this->assertSoftDeleted('opiniones', ['id' => $this->opinion->getKey()]);
    $this->actingAs($this->moderator)->get(route('moderador.opiniones', ['estado' => 'eliminada']))->assertSee('Opinión pendiente del comprador')->assertDontSee('data-mod-delete', false);
    $this->actingAs($this->buyer)->post(route('comprador.opiniones.store', ['tiendas', $this->shop->getKey()]), ['comentario' => 'Una nueva opinión publicada directamente', 'calificacion' => 5])->assertSessionHasNoErrors();
    $this->actingAs($this->seller)->get(route('tiendas.show', $this->shop))->assertSee('Una nueva opinión publicada directamente')->assertSee('5.0 de 5');
});
test('private seller documents can be previewed and approved without granting administrative powers', function () {
    foreach (['identificacion_frente', 'constancia_fiscal', 'comprobante_domicilio'] as $type) {
        $file = UploadedFile::fake()->image($type.'.png', 100, 100);
        $path = $file->store('seller-documents', 'local');
        $this->application->documentos()->create(['tipo_documento' => $type, 'ruta_archivo' => $path, 'nombre_original' => $file->getClientOriginalName(), 'mime_type' => 'image/png', 'tamano' => $file->getSize(), 'fecha_subida' => now()]);
    }
    $document = $this->application->documentos()->first();
    $preview = route('moderador.solicitudes.preview', [$this->application, $document]);
    $this->actingAs($this->buyer)->get($preview)->assertForbidden();
    $this->actingAs($this->moderator)->get(route('moderador.solicitudes.show', $this->application))->assertOk()->assertSee($preview, false)->assertDontSee('/admin/solicitudes-vendedor', false);
    $this->get($preview)->assertOk()->assertHeader('Content-Type', 'image/png')->assertHeader('Cache-Control', 'no-store, private');
    $other = $this->seller->solicitudVendedor()->create(['estado' => 'en_revision']);
    $this->get(route('moderador.solicitudes.preview', [$other, $document]))->assertNotFound();
    $this->post(route('moderador.solicitudes.review', $this->application), ['estado' => 'aprobada'])->assertSessionHasNoErrors();
    expect($this->application->fresh()->estado)->toBe('aprobada')->and($this->buyer->fresh()->tieneTipo('vendedor'))->toBeTrue()->and($this->buyer->notifications()->count())->toBe(1)->and(RegistroActividad::first()->ruta)->toBe('moderador.solicitudes.review');
});

test('buyers can report published content once and receive a response when a moderator resolves it', function () {
    $this->product->forceFill(['estado_moderacion' => 'aprobada'])->save();
    $reportUrl = route('comprador.reportes.store', ['productos', $this->product->getKey()]);
    $data = ['categoria' => 'informacion_falsa', 'motivo' => 'La descripción y el precio de esta publicación son incorrectos.'];
    $this->actingAs($this->buyer)->post($reportUrl, $data)->assertSessionHasNoErrors();
    $this->post($reportUrl, $data)->assertSessionHasNoErrors();
    expect(ReporteContenido::count())->toBe(1);
    $report = ReporteContenido::first();
    $this->actingAs($this->moderator)->get(route('moderador.reportes'))->assertSee($data['motivo'])->assertSee('Publicación para revisar');
    $this->post(route('moderador.reportes.resolve', $report), ['respuesta' => 'Revisamos el contenido y solicitamos la corrección del precio.'])->assertSessionHasNoErrors();
    expect($report->fresh()->estado)->toBe('resuelto')->and($this->buyer->notifications()->count())->toBe(1)->and(RegistroActividad::first()->accion)->toBe('Resolvió un reporte');
    $this->postJson(route('moderador.reportes.resolve', $report), ['respuesta' => 'No debe volver a resolverse este reporte.'])->assertUnprocessable();
    expect($this->buyer->notifications()->count())->toBe(1);
});

test('editing products and comments publishes the new version immediately', function () {
    $this->actingAs($this->seller)->put(route('vendedor.productos.update', $this->product), ['nombre_producto' => 'Versión editada', 'descripcion' => 'Una nueva descripción.', 'precio' => 100, 'id_categoria' => $this->category->getKey(), 'estado_moderacion' => 'pendiente'])->assertSessionHasNoErrors();
    expect($this->product->fresh()->estado_moderacion)->toBe('aprobada')->and($this->product->fresh()->revision_contenido)->toBe(2);
    $this->actingAs($this->buyer)->get(route('comprador.dashboard'))->assertSee('Versión editada');
    $this->post(route('comprador.opiniones.store', ['tiendas', $this->shop->getKey()]), ['comentario' => 'Comentario editado sin revisión previa', 'calificacion' => 5, 'estado_moderacion' => 'pendiente'])->assertSessionHasNoErrors();
    expect($this->opinion->fresh()->estado_moderacion)->toBe('aprobada')->and($this->opinion->fresh()->revision_contenido)->toBe(2);
    $this->actingAs($this->seller)->get(route('tiendas.show', $this->shop))->assertSee('Comentario editado sin revisión previa')->assertSee('5.0 de 5');
    $this->actingAs($this->moderator)->deleteJson(route('moderador.contenido.destroy', ['productos', $this->product->getKey()]), ['version' => 1, 'motivo' => 'No eliminar una versión que fue editada.'])->assertUnprocessable()->assertJsonValidationErrors('version');
});

test('deleted product images are visible only to their owner and verified staff', function () {
    $filename = '11111111-2222-4333-8444-555555555555.png';
    $file = UploadedFile::fake()->image('image.png');
    Storage::disk('local')->put('tiendas/'.$this->shop->getKey().'/'.$filename, file_get_contents($file->getPathname()));
    $url = '/media/tiendas/'.$this->shop->getKey().'/'.$filename;
    $this->product->update(['imagen_url' => $url]);
    $this->get($url)->assertOk();
    $this->actingAs($this->moderator)->delete(route('moderador.contenido.destroy', ['productos', $this->product->getKey()]), ['version' => 1, 'motivo' => 'Imagen inapropiada para el marketplace.'])->assertSessionHasNoErrors();
    $this->actingAs($this->buyer)->get($url)->assertNotFound();
    $this->actingAs($this->seller)->get($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    $this->actingAs($this->moderator)->get($url)->assertOk();
});

test('analytics count deletions and keep seller approval separate', function () {
    $this->actingAs($this->moderator)->delete(route('moderador.contenido.destroy', ['productos', $this->product->getKey()]), ['version' => 1, 'motivo' => 'Contenido inapropiado en esta publicación.'])->assertSessionHasNoErrors();
    $this->get(route('moderador.dashboard'))->assertViewHas('deletedToday', 1);
    $this->get(route('moderador.analiticas'))->assertViewHas('days', fn ($days) => $days->sum('eliminaciones') === 1 && $days->sum('vendedores') === 0);
});
