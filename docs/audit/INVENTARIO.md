# Inventario reproducible de NexoEco

Fecha: 2026-11-04. Generado desde el árbol, route:list, lockfiles y fixtures de prueba. No consulta la base de datos de trabajo.

- 190 rutas registradas; 136 plantillas Blade; 208 archivos PHP enumerados en app, routes, config, database y tests.
- 71 variantes de pantalla renderizadas mediante solicitudes HTTP en SQLite en memoria.
- “Referenciada en pruebas” identifica una referencia literal en el código de la suite, no es una medición de cobertura de todas las ramas.
- Las vistas solo inventariadas no se presentan como revisadas visualmente. Las inclusiones condicionales requieren comprobar la rama correspondiente.

## Versiones resueltas

| Paquete | Versión del lockfile |
|---|---|
| laravel/framework | v12.53.0 |
| laravel/breeze | v2.3.8 |
| munafio/chatify | 2.0.0-beta.2 |
| pestphp/pest | v4.4.1 |
| phpunit/phpunit | 12.5.12 |
| vite | 7.3.1 |
| tailwindcss | 3.4.19 |
| alpinejs | 3.15.8 |
| axios | 1.13.6 |
| leaflet | 1.9.4 |

## Rutas

| Método | URI | Nombre | Acción | Cobertura demostrada o pendiente |
|---|---|---|---|---|
| GET/HEAD | `/` | marketplace.index | `App\Http\Controllers\MarketplaceController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `_boost/browser-logs` | boost.browser-logs | `Closure` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `admin/catalogo/{tipo}` | admin.catalogo | `App\Http\Controllers\AdminCatalogController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `admin/consultas` | admin.consultas.index | `App\Http\Controllers\AdminSupportController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `admin/consultas/{solicitud}` | admin.consultas.show | `App\Http\Controllers\AdminSupportController@show` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `admin/consultas/{solicitud}/responder` | admin.consultas.reply | `App\Http\Controllers\AdminSupportController@reply` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `admin/opiniones` | admin.opiniones | `App\Http\Controllers\AdminCatalogController@reviews` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `admin/productos/{producto}` | admin.productos.show | `App\Http\Controllers\AdminCatalogController@product` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `admin/solicitudes-vendedor` | admin.solicitudes.index | `App\Http\Controllers\AdminSellerApplicationController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `admin/solicitudes-vendedor/{solicitud}` | admin.solicitudes.show | `App\Http\Controllers\AdminSellerApplicationController@show` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `admin/solicitudes-vendedor/{solicitud}/documentos/{documento}` | admin.solicitudes.document | `App\Http\Controllers\AdminSellerApplicationController@document` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `admin/solicitudes-vendedor/{solicitud}/documentos/{documento}/preview` | admin.solicitudes.preview | `App\Http\Controllers\AdminSellerApplicationController@preview` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `admin/solicitudes-vendedor/{solicitud}/revisar` | admin.solicitudes.review | `App\Http\Controllers\AdminSellerApplicationController@review` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `admin/tiendas/{tienda}` | admin.tiendas.show | `App\Http\Controllers\AdminCatalogController@store` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `admin/users` | admin.users.index | `App\Http\Controllers\AdminUserController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `admin/users` | admin.users.store | `App\Http\Controllers\AdminUserController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `admin/users/create` | admin.users.create | `App\Http\Controllers\AdminUserController@create` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `admin/users/{user}` | admin.users.show | `App\Http\Controllers\AdminUserController@show` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| PUT | `admin/users/{user}` | admin.users.update | `App\Http\Controllers\AdminUserController@update` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| DELETE | `admin/users/{user}` | admin.users.destroy | `App\Http\Controllers\AdminUserController@destroy` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `admin/users/{user}/edit` | admin.users.edit | `App\Http\Controllers\AdminUserController@edit` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `administrador/dashboard` | administrador.dashboard | `App\Http\Controllers\Dashboard_Admin_Controller@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `apartados` | apartados.index | `App\Http\Controllers\ApartadoController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `apartados/{apartado}/actualizar` | apartados.sync | `App\Http\Controllers\ApartadoController@sync` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `apartados/{apartado}/continuar` | apartados.resume | `App\Http\Controllers\ApartadoController@resume` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `apartados/{apartado}/retorno` | apartados.return | `App\Http\Controllers\ApartadoController@returned` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `api/chatify/v1/attachments/{filename}` | — | `Chatify\Http\Controllers\Api\AttachmentController@download` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/blocks` | — | `Chatify\Http\Controllers\Api\BlockController@index` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/blocks/{user}` | — | `Chatify\Http\Controllers\Api\BlockController@store` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| DELETE | `api/chatify/v1/blocks/{user}` | — | `Chatify\Http\Controllers\Api\BlockController@destroy` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/broadcasting/auth` | — | `Chatify\Http\Controllers\Api\BroadcastAuthController` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/contacts/search` | — | `Chatify\Http\Controllers\Api\ContactController@search` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/conversations` | — | `Chatify\Http\Controllers\Api\ConversationController@index` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/direct` | — | `Chatify\Http\Controllers\Api\ConversationController@storeDirect` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/group` | — | `Chatify\Http\Controllers\Api\ConversationController@storeGroup` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| PUT | `api/chatify/v1/conversations/pin-order` | — | `Chatify\Http\Controllers\Api\ConversationController@reorderPinned` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/conversations/{conversation}` | — | `Chatify\Http\Controllers\Api\ConversationController@show` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| PATCH | `api/chatify/v1/conversations/{conversation}` | — | `Chatify\Http\Controllers\Api\ConversationController@update` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| DELETE | `api/chatify/v1/conversations/{conversation}` | — | `Chatify\Http\Controllers\Api\ConversationController@destroy` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/conversations/{conversation}/attachments` | — | `Chatify\Http\Controllers\Api\AttachmentController@index` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/avatar` | — | `Chatify\Http\Controllers\Api\ConversationController@uploadAvatar` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/clear` | — | `Chatify\Http\Controllers\Api\ConversationController@clear` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/forward` | — | `Chatify\Http\Controllers\Api\MessageController@forward` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/hide` | — | `Chatify\Http\Controllers\Api\ConversationController@hide` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/leave` | — | `Chatify\Http\Controllers\Api\ConversationController@leave` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/conversations/{conversation}/messages` | — | `Chatify\Http\Controllers\Api\MessageController@index` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/messages` | — | `Chatify\Http\Controllers\Api\MessageController@store` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/conversations/{conversation}/messages/search` | — | `Chatify\Http\Controllers\Api\MessageController@search` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/participants` | — | `Chatify\Http\Controllers\Api\ConversationController@addParticipants` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/conversations/{conversation}/participants` | — | `Chatify\Http\Controllers\Api\GroupParticipantController@index` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| PATCH | `api/chatify/v1/conversations/{conversation}/participants/{user}` | — | `Chatify\Http\Controllers\Api\GroupParticipantController@update` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| DELETE | `api/chatify/v1/conversations/{conversation}/participants/{user}` | — | `Chatify\Http\Controllers\Api\ConversationController@removeParticipant` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| PATCH | `api/chatify/v1/conversations/{conversation}/pin` | — | `Chatify\Http\Controllers\Api\ConversationController@pin` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/read` | — | `Chatify\Http\Controllers\Api\ConversationController@markRead` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/transfer-ownership` | — | `Chatify\Http\Controllers\Api\GroupParticipantController@transferOwnership` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/conversations/{conversation}/typing` | — | `Chatify\Http\Controllers\Api\TypingController@store` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/favorites` | — | `Chatify\Http\Controllers\Api\FavoriteController@index` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/favorites/{user}` | — | `Chatify\Http\Controllers\Api\FavoriteController@toggle` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/link-preview` | — | `Chatify\Http\Controllers\Api\LinkPreviewController@show` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| PATCH | `api/chatify/v1/messages/{message}` | — | `Chatify\Http\Controllers\Api\MessageController@update` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| DELETE | `api/chatify/v1/messages/{message}` | — | `Chatify\Http\Controllers\Api\MessageController@destroy` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/presence/heartbeat` | — | `Chatify\Http\Controllers\Api\PresenceController@heartbeat` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/presence/offline` | — | `Chatify\Http\Controllers\Api\PresenceController@offline` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/settings` | — | `Chatify\Http\Controllers\Api\UserSettingsController@show` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| PATCH | `api/chatify/v1/settings` | — | `Chatify\Http\Controllers\Api\UserSettingsController@update` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/settings/avatar` | — | `Chatify\Http\Controllers\Api\UserSettingsController@updateAvatar` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `api/chatify/v1/settings/chat-background` | — | `Chatify\Http\Controllers\Api\UserSettingsController@updateChatBackground` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/translations` | — | `Chatify\Http\Controllers\Api\TranslationsController` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `api/chatify/v1/users/{user}` | — | `Chatify\Http\Controllers\Api\UserController@show` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `comprador/dashboard` | comprador.dashboard | `App\Http\Controllers\MarketplaceController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `comprador/favoritos/{tipo}` | comprador.favoritos | `App\Http\Controllers\FavoritoController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `comprador/favoritos/{tipo}/{id}` | comprador.favoritos.store | `App\Http\Controllers\FavoritoController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| DELETE | `comprador/favoritos/{tipo}/{id}` | comprador.favoritos.destroy | `App\Http\Controllers\FavoritoController@destroy` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `comprador/notificaciones` | comprador.notificaciones | `App\Http\Controllers\BuyerNotificationController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `comprador/notificaciones/conteo` | comprador.notificaciones.count | `App\Http\Controllers\BuyerNotificationController@count` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `comprador/notificaciones/leer-todas` | comprador.notificaciones.readAll | `App\Http\Controllers\BuyerNotificationController@readAll` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `comprador/notificaciones/{notification}/leer` | comprador.notificaciones.read | `App\Http\Controllers\BuyerNotificationController@read` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `comprador/opiniones` | comprador.opiniones | `App\Http\Controllers\OpinionController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| DELETE | `comprador/opiniones/{opinion}` | comprador.opiniones.destroy | `App\Http\Controllers\OpinionController@destroy` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `comprador/opiniones/{tipo}/{id}` | comprador.opiniones.store | `App\Http\Controllers\OpinionController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `comprador/reportes/{tipo}/{id}` | comprador.reportes.store | `App\Http\Controllers\ContentReportController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `comprador/soporte/consultas/{solicitud}` | comprador.soporte.show | `App\Http\Controllers\BuyerSupportController@thread` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `comprador/soporte/consultas/{solicitud}` | comprador.soporte.reply | `App\Http\Controllers\BuyerSupportController@reply` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `comprador/soporte/contacto` | comprador.soporte.store | `App\Http\Controllers\BuyerSupportController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `comprador/soporte/{seccion}` | comprador.soporte | `App\Http\Controllers\BuyerSupportController@show` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `comprador/tiendas-seguidas` | comprador.tiendas-seguidas | `App\Http\Controllers\StoreAudienceController@followed` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `conocenos` | conocenos | `Illuminate\Routing\ViewController` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `email/verification-notification` | verification.send | `App\Http\Controllers\Auth\NexoEmailVerificationController@send` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `email/verify` | verification.notice | `App\Http\Controllers\Auth\NexoEmailVerificationController@notice` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `email/verify` | verification.verify | `App\Http\Controllers\Auth\NexoEmailVerificationController@verify` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `forgot-password` | password.request | `App\Http\Controllers\Auth\PasswordResetLinkController@create` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `forgot-password` | password.email | `App\Http\Controllers\Auth\PasswordResetLinkController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `login` | login | `App\Http\Controllers\Auth\AuthenticatedSessionController@create` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `login` | login.store | `App\Http\Controllers\Auth\AuthenticatedSessionController@store` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `logout` | logout | `App\Http\Controllers\Auth\AuthenticatedSessionController@destroy` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `media/tiendas/{tienda}/{archivo}` | tiendas.media | `App\Http\Controllers\ShopMediaController@show` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `mensajes` | chat.index | `App\Http\Controllers\ProductChatController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `mensajes/{chat}` | chat.show | `App\Http\Controllers\ProductChatController@show` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `mensajes/{chat}/mensajes` | chat.messages | `App\Http\Controllers\ProductChatController@messages` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `mensajes/{chat}/mensajes` | chat.send | `App\Http\Controllers\ProductChatController@send` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `mercadopago/suscripciones/webhook` | suscripciones.webhook | `App\Http\Controllers\SellerSubscriptionController@webhook` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `mercadopago/webhook` | mercadopago.webhook | `App\Http\Controllers\ApartadoController@webhook` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `moderador/analiticas` | moderador.analiticas | `App\Http\Controllers\ModeratorController@analytics` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `moderador/ayuda` | moderador.ayuda | `App\Http\Controllers\ModeratorController@help` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `moderador/configuracion` | moderador.configuracion | `App\Http\Controllers\ModeratorController@settings` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| DELETE | `moderador/contenido/{tipo}/{id}` | moderador.contenido.destroy | `App\Http\Controllers\ModeratorController@destroy` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `moderador/dashboard` | moderador.dashboard | `App\Http\Controllers\ModeratorController@dashboard` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `moderador/opiniones` | moderador.opiniones | `App\Http\Controllers\ModeratorController@opinions` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `moderador/publicaciones` | moderador.publicaciones | `App\Http\Controllers\ModeratorController@publications` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `moderador/reportes` | moderador.reportes | `App\Http\Controllers\ModeratorController@reports` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `moderador/reportes/{reporte}/resolver` | moderador.reportes.resolve | `App\Http\Controllers\ModeratorController@resolve` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `moderador/vendedores` | moderador.vendedores | `App\Http\Controllers\ModeratorController@sellers` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `moderador/vendedores/{solicitud}` | moderador.solicitudes.show | `App\Http\Controllers\ModeratorController@application` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `moderador/vendedores/{solicitud}/documentos/{documento}` | moderador.solicitudes.document | `App\Http\Controllers\AdminSellerApplicationController@document` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `moderador/vendedores/{solicitud}/documentos/{documento}/preview` | moderador.solicitudes.preview | `App\Http\Controllers\AdminSellerApplicationController@preview` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `moderador/vendedores/{solicitud}/revisar` | moderador.solicitudes.review | `App\Http\Controllers\AdminSellerApplicationController@review` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| PUT | `password` | password.update | `App\Http\Controllers\Auth\PasswordController@update` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `productos/{producto}` | productos.show | `App\Http\Controllers\ProductoController@show` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `productos/{producto}/apartados` | apartados.store | `App\Http\Controllers\ApartadoController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `productos/{producto}/chat` | chat.start | `App\Http\Controllers\ProductChatController@start` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `profile` | profile.edit | `App\Http\Controllers\ProfileController@edit` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| PATCH | `profile` | profile.update | `App\Http\Controllers\ProfileController@update` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| DELETE | `profile` | profile.destroy | `App\Http\Controllers\ProfileController@destroy` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `profile/photo` | profile.photo.store | `App\Http\Controllers\ProfilePhotoController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| DELETE | `profile/photo` | profile.photo.destroy | `App\Http\Controllers\ProfilePhotoController@destroy` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `register` | register | `App\Http\Controllers\Auth\RegisteredUserController@create` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `register` | — | `App\Http\Controllers\Auth\RegisteredUserController@store` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| POST | `reset-password` | password.store | `App\Http\Controllers\Auth\NewPasswordController@store` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `reset-password/{token}` | password.reset | `Closure` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `storage/{path}` | storage.local | `Closure` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| PUT | `storage/{path}` | storage.local.upload | `Closure` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `tiendas/{tienda}` | tiendas.show | `App\Http\Controllers\TiendaController@show` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `tiendas/{tienda}/ruta` | tiendas.ruta | `App\Http\Controllers\StoreRouteController@show` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `tiendas/{tienda}/seguir` | tiendas.follow | `App\Http\Controllers\StoreAudienceController@follow` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `tipos-usuario` | tipos-usuario.index | `App\Http\Controllers\TipoUsuarioController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `tipos-usuario` | tipos-usuario.store | `App\Http\Controllers\TipoUsuarioController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `tipos-usuario/create` | tipos-usuario.create | `App\Http\Controllers\TipoUsuarioController@create` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `tipos-usuario/{tipos_usuario}` | tipos-usuario.show | `App\Http\Controllers\TipoUsuarioController@show` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| PUT/PATCH | `tipos-usuario/{tipos_usuario}` | tipos-usuario.update | `App\Http\Controllers\TipoUsuarioController@update` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| DELETE | `tipos-usuario/{tipos_usuario}` | tipos-usuario.destroy | `App\Http\Controllers\TipoUsuarioController@destroy` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `tipos-usuario/{tipos_usuario}/edit` | tipos-usuario.edit | `App\Http\Controllers\TipoUsuarioController@edit` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `ubicacion` | marketplace.ubicacion.update | `App\Http\Controllers\BuyerLocationController@update` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `up` | — | `Closure` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `usuarios/{user}/foto` | profile.photo.show | `App\Http\Controllers\ProfilePhotoController@show` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/apartados` | vendedor.apartados.index | `App\Http\Controllers\ApartadoController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| PUT | `vendedor/apartados/{apartado}/seguimiento` | vendedor.apartados.seguimiento | `App\Http\Controllers\SellerToolsController@fulfillment` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/calendario` | vendedor.calendario | `App\Http\Controllers\SellerToolsController@calendar` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `vendedor/dashboard` | vendedor.dashboard | `App\Http\Controllers\SellerStoreController@dashboard` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `vendedor/estadisticas` | vendedor.estadisticas.index | `App\Http\Controllers\SellerAnalyticsController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `vendedor/estadisticas/reporte` | vendedor.estadisticas.export | `App\Http\Controllers\SellerAnalyticsController@export` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/herramientas` | vendedor.herramientas.index | `App\Http\Controllers\SellerToolsController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| PUT | `vendedor/herramientas/automatizaciones` | vendedor.herramientas.automatizaciones | `App\Http\Controllers\SellerToolsController@automation` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `vendedor/herramientas/fijados/{producto}` | vendedor.herramientas.fijados | `App\Http\Controllers\SellerToolsController@pin` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| PUT | `vendedor/herramientas/personalizacion` | vendedor.herramientas.personalizacion | `App\Http\Controllers\SellerToolsController@design` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `vendedor/herramientas/respuestas` | vendedor.herramientas.respuestas.store | `App\Http\Controllers\SellerToolsController@saveReply` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| PUT | `vendedor/herramientas/respuestas/{reply}` | vendedor.herramientas.respuestas.update | `App\Http\Controllers\SellerToolsController@saveReply` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| DELETE | `vendedor/herramientas/respuestas/{reply}` | vendedor.herramientas.respuestas.destroy | `App\Http\Controllers\SellerToolsController@deleteReply` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/mensajes` | vendedor.mensajes.index | `App\Http\Controllers\ProductChatController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| PUT | `vendedor/mensajes/{chat}/negociacion` | vendedor.mensajes.negociacion | `App\Http\Controllers\SellerToolsController@negotiation` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `vendedor/mercadopago/conectar` | vendedor.mercadopago.connect | `App\Http\Controllers\MercadoPagoConnectionController@connect` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/mercadopago/retorno` | vendedor.mercadopago.callback | `App\Http\Controllers\MercadoPagoConnectionController@callback` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/notificaciones` | vendedor.notificaciones | `App\Http\Controllers\BuyerNotificationController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `vendedor/novedades` | vendedor.novedades.index | `App\Http\Controllers\StoreAudienceController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `vendedor/novedades` | vendedor.novedades.store | `App\Http\Controllers\StoreAudienceController@save` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| DELETE | `vendedor/novedades/{update}` | vendedor.novedades.destroy | `App\Http\Controllers\StoreAudienceController@destroy` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/productos` | vendedor.productos.index | `App\Http\Controllers\SellerProductController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `vendedor/productos` | vendedor.productos.store | `App\Http\Controllers\SellerProductController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/productos/crear` | vendedor.productos.create | `App\Http\Controllers\SellerProductController@create` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| PUT | `vendedor/productos/{producto}` | vendedor.productos.update | `App\Http\Controllers\SellerProductController@update` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/productos/{producto}/editar` | vendedor.productos.edit | `App\Http\Controllers\SellerProductController@edit` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `vendedor/promociones` | vendedor.promociones.index | `App\Http\Controllers\SellerOfferController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `vendedor/promociones` | vendedor.promociones.store | `App\Http\Controllers\SellerOfferController@save` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| PUT | `vendedor/promociones/{offer}` | vendedor.promociones.update | `App\Http\Controllers\SellerOfferController@save` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| DELETE | `vendedor/promociones/{offer}` | vendedor.promociones.destroy | `App\Http\Controllers\SellerOfferController@disable` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/registro` | vendedor.register | `App\Http\Controllers\Auth\SellerRegistrationController@create` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `vendedor/registro` | — | `App\Http\Controllers\Auth\SellerRegistrationController@store` | Inventariada; sin ejecución individual demostrada en esta auditoría |
| GET/HEAD | `vendedor/suscripcion` | vendedor.suscripcion.index | `App\Http\Controllers\SellerSubscriptionController@index` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| POST | `vendedor/suscripcion/contratar` | vendedor.suscripcion.checkout | `App\Http\Controllers\SellerSubscriptionController@checkout` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `vendedor/suscripcion/gratis` | vendedor.suscripcion.gratis | `App\Http\Controllers\SellerSubscriptionController@activarPlanGratuito` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `vendedor/suscripcion/productos/{producto}` | vendedor.suscripcion.promote | `App\Http\Controllers\SellerSubscriptionController@promote` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `vendedor/suscripcion/{subscription}/actualizar` | vendedor.suscripcion.sync | `App\Http\Controllers\SellerSubscriptionController@sync` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `vendedor/suscripcion/{subscription}/cancelar` | vendedor.suscripcion.cancel | `App\Http\Controllers\SellerSubscriptionController@cancel` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/suscripcion/{subscription}/retorno` | vendedor.suscripcion.return | `App\Http\Controllers\SellerSubscriptionController@returned` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `vendedor/tienda` | vendedor.tienda.store | `App\Http\Controllers\SellerStoreController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| PUT | `vendedor/tienda` | vendedor.tienda.update | `App\Http\Controllers\SellerStoreController@update` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| GET/HEAD | `vendedor/tienda/crear` | vendedor.tienda.create | `App\Http\Controllers\SellerStoreController@create` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `vendedor/tienda/editar` | vendedor.tienda.edit | `App\Http\Controllers\SellerStoreController@edit` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `vender` | vender | `Closure` | Render HTTP 200 + análisis responsive/axe en variantes del manifest |
| GET/HEAD | `vender/solicitud` | vendedor.solicitud.create | `App\Http\Controllers\SolicitudVendedorController@create` | Referenciada en suite PHP ejecutada; revisar escenario en tests |
| POST | `vender/solicitud` | vendedor.solicitud.store | `App\Http\Controllers\SolicitudVendedorController@store` | Referenciada en suite PHP ejecutada; revisar escenario en tests |

## Variantes de pantalla

| ID de evidencia | Rol | Ruta | Vista de entrada |
|---|---|---|---|
| 00-publico-marketplace.index | Público | marketplace.index | marketplace.index |
| 01-publico-vender | Público | vender | welcome |
| 02-publico-conocenos | Público | conocenos | conocenos |
| 03-publico-login | Público | login | auth.login |
| 04-publico-register | Público | register | auth.register |
| 05-publico-password.request | Público | password.request | auth.forgot-password |
| 06-publico-vendedor.register | Público | vendedor.register | auth.seller-register |
| 07-publico-password.reset | Público | password.reset | auth.reset-password |
| 08-comprador-comprador.dashboard | comprador | comprador.dashboard | marketplace.index |
| 09-comprador-comprador.opiniones | comprador | comprador.opiniones | comprador.opiniones |
| 10-comprador-comprador.notificaciones | comprador | comprador.notificaciones | comprador.notificaciones |
| 11-comprador-chat.index | comprador | chat.index | chat.index |
| 12-comprador-apartados.index | comprador | apartados.index | apartados.index |
| 13-comprador-profile.edit | comprador | profile.edit | profile.edit |
| 14-comprador-comprador.favoritos | comprador | comprador.favoritos | comprador.favoritos |
| 15-comprador-comprador.favoritos | comprador | comprador.favoritos | comprador.favoritos |
| 16-comprador-comprador.soporte | comprador | comprador.soporte | comprador.soporte |
| 17-comprador-comprador.soporte | comprador | comprador.soporte | comprador.soporte |
| 18-comprador-comprador.soporte | comprador | comprador.soporte | comprador.soporte |
| 19-comprador-comprador.soporte.show | comprador | comprador.soporte.show | comprador.consulta |
| 20-comprador-productos.show | comprador | productos.show | marketplace.producto |
| 21-comprador-tiendas.show | comprador | tiendas.show | marketplace.tienda |
| 22-comprador-chat.show | comprador | chat.show | chat.show |
| 23-vendedor-vendedor.dashboard | vendedor | vendedor.dashboard | vendedor.dashboard |
| 24-vendedor-vendedor.tienda.edit | vendedor | vendedor.tienda.edit | vendedor.tienda-form |
| 25-vendedor-vendedor.productos.index | vendedor | vendedor.productos.index | vendedor.productos.index |
| 26-vendedor-vendedor.productos.create | vendedor | vendedor.productos.create | vendedor.productos.form |
| 27-vendedor-vendedor.mensajes.index | vendedor | vendedor.mensajes.index | chat.index |
| 28-vendedor-vendedor.apartados.index | vendedor | vendedor.apartados.index | apartados.index |
| 29-vendedor-vendedor.suscripcion.index | vendedor | vendedor.suscripcion.index | vendedor.suscripcion |
| 30-vendedor-vendedor.herramientas.index | vendedor | vendedor.herramientas.index | vendedor.herramientas |
| 31-vendedor-vendedor.calendario | vendedor | vendedor.calendario | vendedor.calendario |
| 32-vendedor-vendedor.promociones.index | vendedor | vendedor.promociones.index | vendedor.promociones |
| 33-vendedor-vendedor.novedades.index | vendedor | vendedor.novedades.index | vendedor.novedades |
| 34-vendedor-vendedor.estadisticas.index | vendedor | vendedor.estadisticas.index | vendedor.estadisticas |
| 35-vendedor-vendedor.notificaciones | vendedor | vendedor.notificaciones | comprador.notificaciones |
| 36-vendedor-vendedor.productos.edit | vendedor | vendedor.productos.edit | vendedor.productos.form |
| 37-vendedor-chat.show | vendedor | chat.show | chat.show |
| 38-moderador-moderador.dashboard | moderador | moderador.dashboard | moderador.dashboard |
| 39-moderador-moderador.publicaciones | moderador | moderador.publicaciones | moderador.publicaciones |
| 40-moderador-moderador.opiniones | moderador | moderador.opiniones | moderador.opiniones |
| 41-moderador-moderador.reportes | moderador | moderador.reportes | moderador.reportes |
| 42-moderador-moderador.vendedores | moderador | moderador.vendedores | moderador.vendedores |
| 43-moderador-moderador.analiticas | moderador | moderador.analiticas | moderador.analiticas |
| 44-moderador-moderador.configuracion | moderador | moderador.configuracion | moderador.configuracion |
| 45-moderador-moderador.ayuda | moderador | moderador.ayuda | moderador.ayuda |
| 46-moderador-moderador.solicitudes.show | moderador | moderador.solicitudes.show | moderador.solicitud |
| 47-administrador-administrador.dashboard | administrador | administrador.dashboard | administrador.dashboard |
| 48-administrador-admin.users.index | administrador | admin.users.index | administrador.usuarios.index |
| 49-administrador-admin.users.create | administrador | admin.users.create | administrador.usuarios.create |
| 50-administrador-admin.consultas.index | administrador | admin.consultas.index | administrador.consultas.index |
| 51-administrador-admin.solicitudes.index | administrador | admin.solicitudes.index | administrador.solicitudes.index |
| 52-administrador-admin.opiniones | administrador | admin.opiniones | administrador.opiniones |
| 53-administrador-profile.edit | administrador | profile.edit | profile.edit |
| 54-administrador-admin.users.show | administrador | admin.users.show | administrador.usuarios.show |
| 55-administrador-admin.users.edit | administrador | admin.users.edit | administrador.usuarios.edit |
| 56-administrador-admin.consultas.show | administrador | admin.consultas.show | administrador.consultas.show |
| 57-administrador-admin.solicitudes.show | administrador | admin.solicitudes.show | administrador.solicitudes.show |
| 58-administrador-admin.productos.show | administrador | admin.productos.show | administrador.producto |
| 59-administrador-admin.tiendas.show | administrador | admin.tiendas.show | administrador.tienda |
| 60-administrador-admin.catalogo | administrador | admin.catalogo | administrador.catalogo |
| 61-administrador-admin.catalogo | administrador | admin.catalogo | administrador.catalogo |
| 62-administrador-tipos-usuario.index | administrador | tipos-usuario.index | tipos_usuario.index |
| 63-administrador-tipos-usuario.create | administrador | tipos-usuario.create | tipos_usuario.create |
| 64-administrador-tipos-usuario.show | administrador | tipos-usuario.show | tipos_usuario.show |
| 65-administrador-tipos-usuario.edit | administrador | tipos-usuario.edit | tipos_usuario.edit |
| 66-sin-verificar-verification.notice | sin-verificar | verification.notice | auth.verify-email |
| 67-vendedor-nuevo-vendedor.tienda.create | vendedor-nuevo | vendedor.tienda.create | vendedor.tienda-form |
| 68-vendedor-nuevo-vendedor.herramientas.index | vendedor-nuevo | vendedor.herramientas.index | vendedor.herramientas |
| 69-vendedor-nuevo-vendedor.suscripcion.index | vendedor-nuevo | vendedor.suscripcion.index | vendedor.suscripcion |
| 70-comprador-vendedor.register | comprador | vendedor.register | auth.seller-register |

## Todas las vistas

| Vista | Evidencia |
|---|---|
| `administrador.catalogo` | Render HTTP demostrado; ver manifest |
| `administrador.consultas.index` | Render HTTP demostrado; ver manifest |
| `administrador.consultas.show` | Render HTTP demostrado; ver manifest |
| `administrador.dashboard` | Render HTTP demostrado; ver manifest |
| `administrador.opiniones` | Render HTTP demostrado; ver manifest |
| `administrador.partials.product-card` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `administrador.partials.reviews` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `administrador.partials.store-card` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `administrador.producto` | Render HTTP demostrado; ver manifest |
| `administrador.solicitudes.index` | Render HTTP demostrado; ver manifest |
| `administrador.solicitudes.show` | Render HTTP demostrado; ver manifest |
| `administrador.tienda` | Render HTTP demostrado; ver manifest |
| `administrador.usuarios.create` | Render HTTP demostrado; ver manifest |
| `administrador.usuarios.edit` | Render HTTP demostrado; ver manifest |
| `administrador.usuarios.index` | Render HTTP demostrado; ver manifest |
| `administrador.usuarios.partials.create-form` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `administrador.usuarios.show` | Render HTTP demostrado; ver manifest |
| `apartados.index` | Render HTTP demostrado; ver manifest |
| `auth.confirm-password` | Inventariada/compilable; sin renderizado visual demostrado |
| `auth.forgot-password` | Render HTTP demostrado; ver manifest |
| `auth.login` | Render HTTP demostrado; ver manifest |
| `auth.partials.email-verification` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `auth.partials.seller-styles` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `auth.register` | Render HTTP demostrado; ver manifest |
| `auth.reset-password` | Render HTTP demostrado; ver manifest |
| `auth.seller-register` | Render HTTP demostrado; ver manifest |
| `auth.verify-email` | Render HTTP demostrado; ver manifest |
| `chat.index` | Render HTTP demostrado; ver manifest |
| `chat.show` | Render HTTP demostrado; ver manifest |
| `components.application-logo` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.auth-session-status` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.danger-button` | Inventariada/compilable; sin renderizado visual demostrado |
| `components.dropdown-link` | Inventariada/compilable; sin renderizado visual demostrado |
| `components.dropdown` | Inventariada/compilable; sin renderizado visual demostrado |
| `components.input-error` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.input-label` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.market-icon` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.mod-icon` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.modal` | Inventariada/compilable; sin renderizado visual demostrado |
| `components.nav-link` | Inventariada/compilable; sin renderizado visual demostrado |
| `components.navigation-logo` | Inventariada/compilable; sin renderizado visual demostrado |
| `components.notification-bell` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.primary-button` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.responsive-nav-link` | Inventariada/compilable; sin renderizado visual demostrado |
| `components.secondary-button` | Inventariada/compilable; sin renderizado visual demostrado |
| `components.seller-growth-empty` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.seller-growth-hero` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.seller-icon` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `components.text-input` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `comprador.consulta` | Render HTTP demostrado; ver manifest |
| `comprador.dashboard` | Inventariada/compilable; sin renderizado visual demostrado |
| `comprador.favoritos` | Render HTTP demostrado; ver manifest |
| `comprador.notificaciones` | Render HTTP demostrado; ver manifest |
| `comprador.opiniones` | Render HTTP demostrado; ver manifest |
| `comprador.partials.favorite-store-card` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `comprador.partials.support-messages` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `comprador.soporte` | Render HTTP demostrado; ver manifest |
| `conocenos` | Render HTTP demostrado; ver manifest |
| `emails.seller-documents-ready` | Inventariada/compilable; sin renderizado visual demostrado |
| `emails.verify-email` | Inventariada/compilable; sin renderizado visual demostrado |
| `errors.419` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.admin-panel` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `layouts.administrador.app` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.administrador.guest` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.administrador.navigation` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.administrador.panel-header` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `layouts.administrador.panel-navigation` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `layouts.app` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.base` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `layouts.comprador.app` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.comprador.guest` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.comprador.navigation` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `layouts.guest` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `layouts.marketplace` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `layouts.moderador.app` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.moderador.guest` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.moderador.navigation` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.moderator-panel` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `layouts.seller-panel` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `layouts.vendedor.app` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.vendedor.guest` | Inventariada/compilable; sin renderizado visual demostrado |
| `layouts.vendedor.navigation` | Inventariada/compilable; sin renderizado visual demostrado |
| `marketplace.index` | Render HTTP demostrado; ver manifest |
| `marketplace.partials.business-hours` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.commercial-offer` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.content-report` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.favorite-button` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.location-awareness` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.location-button` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.location-dialog` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.nearby-stores` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.opiniones` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.product-card` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.product-contact` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.review-form` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.route-dialog` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.store-card` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.partials.store-directions` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `marketplace.producto` | Render HTTP demostrado; ver manifest |
| `marketplace.solicitud-vendedor` | Inventariada/compilable; sin renderizado visual demostrado |
| `marketplace.tienda` | Render HTTP demostrado; ver manifest |
| `moderador.analiticas` | Render HTTP demostrado; ver manifest |
| `moderador.ayuda` | Render HTTP demostrado; ver manifest |
| `moderador.configuracion` | Render HTTP demostrado; ver manifest |
| `moderador.dashboard` | Render HTTP demostrado; ver manifest |
| `moderador.opiniones` | Render HTTP demostrado; ver manifest |
| `moderador.partials.application-review` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `moderador.partials.delete-form` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `moderador.partials.status` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `moderador.publicaciones` | Render HTTP demostrado; ver manifest |
| `moderador.reportes` | Render HTTP demostrado; ver manifest |
| `moderador.solicitud` | Render HTTP demostrado; ver manifest |
| `moderador.vendedores` | Render HTTP demostrado; ver manifest |
| `profile.edit` | Render HTTP demostrado; ver manifest |
| `profile.partials.delete-user-form` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `profile.partials.photo-form` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `profile.partials.update-password-form` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `profile.partials.update-profile-information-form` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `tipos_usuario.create` | Render HTTP demostrado; ver manifest |
| `tipos_usuario.edit` | Render HTTP demostrado; ver manifest |
| `tipos_usuario.index` | Render HTTP demostrado; ver manifest |
| `tipos_usuario.partials.form` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `tipos_usuario.show` | Render HTTP demostrado; ver manifest |
| `vendedor.calendario` | Render HTTP demostrado; ver manifest |
| `vendedor.dashboard` | Render HTTP demostrado; ver manifest |
| `vendedor.estadisticas` | Render HTTP demostrado; ver manifest |
| `vendedor.herramientas` | Render HTTP demostrado; ver manifest |
| `vendedor.novedades` | Render HTTP demostrado; ver manifest |
| `vendedor.partials.offer-form` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `vendedor.productos.card` | Dependencia estática de pantalla renderizada; rama condicional no garantizada |
| `vendedor.productos.form` | Render HTTP demostrado; ver manifest |
| `vendedor.productos.index` | Render HTTP demostrado; ver manifest |
| `vendedor.promociones` | Render HTTP demostrado; ver manifest |
| `vendedor.suscripcion` | Render HTTP demostrado; ver manifest |
| `vendedor.tienda-form` | Render HTTP demostrado; ver manifest |
| `welcome` | Render HTTP demostrado; ver manifest |

## Referencias literales a vistas inexistentes

Ninguna en las llamadas view() literales de app/. Las referencias dinámicas no quedan demostradas por este análisis.

## Árbol de código PHP inspeccionable

- `app/Http/Controllers/AdminCatalogController.php`
- `app/Http/Controllers/AdminSellerApplicationController.php`
- `app/Http/Controllers/AdminSupportController.php`
- `app/Http/Controllers/AdminUserController.php`
- `app/Http/Controllers/ApartadoController.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Http/Controllers/Auth/ConfirmablePasswordController.php`
- `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`
- `app/Http/Controllers/Auth/EmailVerificationPromptController.php`
- `app/Http/Controllers/Auth/NewPasswordController.php`
- `app/Http/Controllers/Auth/NexoEmailVerificationController.php`
- `app/Http/Controllers/Auth/PasswordController.php`
- `app/Http/Controllers/Auth/PasswordResetLinkController.php`
- `app/Http/Controllers/Auth/RegisteredUserController.php`
- `app/Http/Controllers/Auth/SellerRegistrationController.php`
- `app/Http/Controllers/Auth/VerifyEmailController.php`
- `app/Http/Controllers/BuyerLocationController.php`
- `app/Http/Controllers/BuyerNotificationController.php`
- `app/Http/Controllers/BuyerSupportController.php`
- `app/Http/Controllers/CategoriaController.php`
- `app/Http/Controllers/ContentReportController.php`
- `app/Http/Controllers/Controller.php`
- `app/Http/Controllers/Dashboard_Admin_Controller.php`
- `app/Http/Controllers/DetallePedidoController.php`
- `app/Http/Controllers/FavoritoController.php`
- `app/Http/Controllers/MarketplaceController.php`
- `app/Http/Controllers/MercadoPagoConnectionController.php`
- `app/Http/Controllers/ModeratorController.php`
- `app/Http/Controllers/OpinionController.php`
- `app/Http/Controllers/PedidoController.php`
- `app/Http/Controllers/ProductChatController.php`
- `app/Http/Controllers/ProductoController.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/ProfilePhotoController.php`
- `app/Http/Controllers/SellerAnalyticsController.php`
- `app/Http/Controllers/SellerOfferController.php`
- `app/Http/Controllers/SellerProductController.php`
- `app/Http/Controllers/SellerStoreController.php`
- `app/Http/Controllers/SellerSubscriptionController.php`
- `app/Http/Controllers/SellerToolsController.php`
- `app/Http/Controllers/ShopMediaController.php`
- `app/Http/Controllers/SolicitudVendedorController.php`
- `app/Http/Controllers/StoreAudienceController.php`
- `app/Http/Controllers/StoreRouteController.php`
- `app/Http/Controllers/TiendaController.php`
- `app/Http/Controllers/TipoUsuarioController.php`
- `app/Http/Middleware/CheckSubscription.php`
- `app/Http/Middleware/DisableChatifyApi.php`
- `app/Http/Middleware/EnsureAccountArea.php`
- `app/Http/Middleware/EnsureActiveAccount.php`
- `app/Http/Middleware/EnsureAdministrator.php`
- `app/Http/Middleware/EnsureMarketplaceMember.php`
- `app/Http/Middleware/EnsureModerator.php`
- `app/Http/Middleware/EnsureSeller.php`
- `app/Http/Middleware/RecordStaffActivity.php`
- `app/Http/Middleware/ValidateCsrfToken.php`
- `app/Http/Requests/AdminUserRequest.php`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/Auth/StoreSellerRegistrationRequest.php`
- `app/Http/Requests/MarketplaceIndexRequest.php`
- `app/Http/Requests/ProfileUpdateRequest.php`
- `app/Http/Requests/StoreBusinessRequest.php`
- `app/Http/Requests/StoreSolicitudVendedorRequest.php`
- `app/Jobs/RetrySellerReviewEmail.php`
- `app/Models/Apartado.php`
- `app/Models/Cache.php`
- `app/Models/CacheLock.php`
- `app/Models/Categoria.php`
- `app/Models/CommercialOffer.php`
- `app/Models/DetallePedido.php`
- `app/Models/DocumentoVendedor.php`
- `app/Models/EstadosPedido.php`
- `app/Models/Job.php`
- `app/Models/JobBatch.php`
- `app/Models/MercadoPagoAccount.php`
- `app/Models/MetodosPago.php`
- `app/Models/Opinion.php`
- `app/Models/Pedido.php`
- `app/Models/ProductChat.php`
- `app/Models/Producto.php`
- `app/Models/ProductoImagen.php`
- `app/Models/RegistroActividad.php`
- `app/Models/ReporteContenido.php`
- `app/Models/RespuestaSoporte.php`
- `app/Models/SellerAutomation.php`
- `app/Models/SellerQuickReply.php`
- `app/Models/SellerStoreDesign.php`
- `app/Models/SellerSubscription.php`
- `app/Models/Session.php`
- `app/Models/SolicitudSoporte.php`
- `app/Models/SolicitudVendedor.php`
- `app/Models/StoreUpdate.php`
- `app/Models/Tienda.php`
- `app/Models/TiposUsuario.php`
- `app/Models/User.php`
- `app/Models/UsuarioTipo.php`
- `app/Notifications/BuyerActivityNotification.php`
- `app/Notifications/SellerDocumentsReady.php`
- `app/Notifications/VerifyNexoEcoEmail.php`
- `app/Observers/ApartadoObserver.php`
- `app/Observers/ProductMessageObserver.php`
- `app/Providers/AppServiceProvider.php`
- `app/Services/AdministratorLimit.php`
- `app/Services/BuyerAccountContext.php`
- `app/Services/EmailVerificationCodeService.php`
- `app/Services/LocalDiscovery.php`
- `app/Services/MercadoPagoConnection.php`
- `app/Services/MercadoPagoReservations.php`
- `app/Services/MercadoPagoSubscriptions.php`
- `app/Services/SellerAnalytics.php`
- `app/Services/SellerBenefits.php`
- `app/Services/SellerChatAutomation.php`
- `app/Services/SellerGrowthScheduler.php`
- `app/Services/SellerPromotion.php`
- `app/Services/SellerRegistrationService.php`
- `app/Services/SellerReviewNotifier.php`
- `app/Services/ShopMedia.php`
- `app/Services/StoreAudience.php`
- `app/Services/UsuarioRolService.php`
- `app/Support/AccountRoles.php`
- `app/Support/DesignTokens.php`
- `app/Support/ManzanilloBoundary.php`
- `app/Support/MarketplaceDepartments.php`
- `app/Support/MarketplaceImage.php`
- `app/Support/RegistrationPassword.php`
- `app/View/Components/AppLayout.php`
- `app/View/Components/GuestLayout.php`
- `config/app.php`
- `config/auth.php`
- `config/cache.php`
- `config/chatify.php`
- `config/database.php`
- `config/discovery.php`
- `config/filesystems.php`
- `config/logging.php`
- `config/mail.php`
- `config/mercadopago.php`
- `config/models.php`
- `config/queue.php`
- `config/services.php`
- `config/session.php`
- `config/subscriptions.php`
- `database/factories/UserFactory.php`
- `database/migrations/2026_05_08_165012_create_cache_locks_table.php`
- `database/migrations/2026_05_08_165012_create_cache_table.php`
- `database/migrations/2026_05_08_165012_create_categorias_table.php`
- `database/migrations/2026_05_08_165012_create_detalle_pedido_table.php`
- `database/migrations/2026_05_08_165012_create_estados_pedido_table.php`
- `database/migrations/2026_05_08_165012_create_failed_jobs_table.php`
- `database/migrations/2026_05_08_165012_create_job_batches_table.php`
- `database/migrations/2026_05_08_165012_create_jobs_table.php`
- `database/migrations/2026_05_08_165012_create_metodos_pago_table.php`
- `database/migrations/2026_05_08_165012_create_password_reset_tokens_table.php`
- `database/migrations/2026_05_08_165012_create_pedidos_table.php`
- `database/migrations/2026_05_08_165012_create_productos_table.php`
- `database/migrations/2026_05_08_165012_create_sessions_table.php`
- `database/migrations/2026_05_08_165012_create_tiendas_table.php`
- `database/migrations/2026_05_08_165012_create_tipos_usuario_table.php`
- `database/migrations/2026_05_08_165012_create_users_table.php`
- `database/migrations/2026_05_08_165012_create_usuario_tipo_table.php`
- `database/migrations/2026_05_08_165015_add_foreign_keys_to_detalle_pedido_table.php`
- `database/migrations/2026_05_08_165015_add_foreign_keys_to_pedidos_table.php`
- `database/migrations/2026_05_08_165015_add_foreign_keys_to_productos_table.php`
- `database/migrations/2026_05_08_165015_add_foreign_keys_to_tiendas_table.php`
- `database/migrations/2026_05_08_165015_add_foreign_keys_to_usuario_tipo_table.php`
- `database/migrations/2026_05_28_172302_add_activo_to_users_table.php`
- `database/migrations/2026_07_18_000000_create_chatify_tables.php`
- `database/migrations/2026_09_18_213841_create_producto_imagenes_table.php`
- `database/migrations/2026_10_02_221100_create_solicitudes_vendedor_table.php`
- `database/migrations/2026_10_02_221102_create_documentos_vendedor_table.php`
- `database/migrations/2026_10_06_230000_create_buyer_account_tables.php`
- `database/migrations/2026_10_07_000000_add_profile_photos_and_support_replies.php`
- `database/migrations/2026_10_07_010000_create_registros_actividad_table.php`
- `database/migrations/2026_10_07_020000_add_store_business_details.php`
- `database/migrations/2026_10_07_030000_add_buyer_location_to_users.php`
- `database/migrations/2026_10_07_040000_add_content_moderation.php`
- `database/migrations/2026_10_07_050000_allow_direct_content_publication.php`
- `database/migrations/2026_10_09_000000_add_product_chat_and_reservations.php`
- `database/migrations/2026_10_09_010000_add_mercado_pago_accounts.php`
- `database/migrations/2026_10_11_000000_create_seller_subscriptions.php`
- `database/migrations/2026_10_11_010000_add_seller_growth_tools.php`
- `database/migrations/2026_10_11_020000_add_commercial_offer_notifications.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/TiposUsuarioSeeder.php`
- `routes/console.php`
- `routes/web.php`
- `tests/Feature/AdminAreaIsolationTest.php`
- `tests/Feature/AdminPanelTest.php`
- `tests/Feature/AdminSecurityAndDocumentsTest.php`
- `tests/Feature/AuditCoverageTest.php`
- `tests/Feature/AuditRegressionTest.php`
- `tests/Feature/BuyerAccountTest.php`
- `tests/Feature/BuyerLocationTest.php`
- `tests/Feature/ExampleTest.php`
- `tests/Feature/MarketplaceDiscoveryAndNotificationsTest.php`
- `tests/Feature/ModeratorPanelTest.php`
- `tests/Feature/ProductChatAndReservationsTest.php`
- `tests/Feature/ProfilePhotoTest.php`
- `tests/Feature/SellerGrowthToolsTest.php`
- `tests/Feature/SellerRegistrationTest.php`
- `tests/Feature/SellerStoreTest.php`
- `tests/Feature/SellerSubscriptionTest.php`
- `tests/Feature/SessionRecoveryTest.php`
- `tests/Feature/StaffActivityTest.php`
- `tests/Pest.php`
- `tests/Support/BuyerDatabase.php`
- `tests/TestCase.php`
- `tests/Unit/ExampleTest.php`
