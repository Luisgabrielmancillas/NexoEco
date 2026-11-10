# Auditoría integral de NexoEco

Fecha: **4 de noviembre de 2026**, Ciudad de México.

## 1. Resumen ejecutivo

Se inventariaron **190 rutas, 136 plantillas Blade y 208 archivos PHP** de aplicación, configuración, rutas, base de datos y pruebas. Se renderizaron **71 variantes de pantalla** con datos ficticios en SQLite en memoria, cubriendo los cuatro roles, acceso, catálogo, productos, tiendas, chat, apartados, soporte, perfil, moderación, administración y herramientas del vendedor.

Se corrigieron errores funcionales y de interfaz directamente en el proyecto: cuatro vistas administrativas inexistentes, cierre de sesión prematuro al intentar eliminar una cuenta con historial comercial, recuperación de contraseña ante fallos de correo, doble envío y recuperación del chat, modales inaccesibles, navegación superpuesta, desbordamiento de la tabla del moderador, errores de contraste y objetivos táctiles. Se ajustó la información comercial de la landing a las funciones y planes existentes.

La suite inicial obtuvo **219 pruebas aprobadas y una fallida**. La regresión completa obtuvo **226 pruebas aprobadas, 2550 aserciones**. Las cuatro pruebas JavaScript también pasan. La compilación de Vite y de todas las vistas Blade finalizó correctamente.

La evidencia de navegador utiliza **HTML real generado por Laravel y recursos compilados**, servido en loopback. El servidor de snapshots no ejecuta los controladores: las mutaciones y autorizaciones se prueban por separado en PHP; el transporte del chat se simula en Playwright. Esto no equivale a probar todos los flujos de extremo a extremo sobre MySQL ni a certificar WCAG AA.

## 2. Proyecto y lógica reconocida

| Tecnología | Versión comprobada |
|---|---|
| PHP / Composer | 8.4.16 / 2.8.11, ejecutables de Herd |
| Laravel / Breeze | 12.53.0 / 2.3.8 |
| Chatify | 2.0.0-beta.2 |
| Pest / PHPUnit | 4.4.1 / 12.5.12 |
| Node / Vite | 24.14.0 / 7.3.1 |
| Tailwind / Alpine | 3.4.19 / 3.15.8 |
| Axios / Leaflet | 1.13.6 / 1.9.4 |
| Navegador de QA | Edge 156.0.4314.58, headless mediante Playwright |

Las versiones de librerías provienen de los lockfiles. El entorno consultado es `local`, con zona de aplicación UTC e idioma configurado `en`; no se modificaron `.env`, secretos ni configuración de producción.

- El catálogo es público; abrir productos o tiendas requiere sesión. Favoritos, opiniones y operaciones comerciales añaden verificación de correo según las rutas existentes.
- Comprador y vendedor pueden compartir una cuenta, con bandejas y apartados separados por participante. Administrador y moderador mantienen áreas aisladas mediante middleware de backend.
- La solicitud de vendedor y sus documentos preceden a la habilitación del rol. Se conservan aprobación, revisión documental y límites administrativos.
- Chat: conversación por producto y comprador, participantes privados, mensajes paginados y notificaciones.
- Apartados: anticipos y condiciones guardados en servidor; conciliación con Mercado Pago, validación de importe, receptor, moneda, firma y modo. No se cambiaron estos servicios ni estados financieros.
- Planes Básico, Impulso y Pro: herramientas, promoción, respuestas rápidas, automatizaciones, ofertas, diseño de tienda, estadísticas y calendario según beneficios existentes.
- `PedidoController` y `DetallePedidoController` contienen métodos vacíos. No se encontró un carrito operativo conectado a las rutas actuales. La landing ya no presenta un seguimiento automatizado de pedidos como función disponible.
- Componentes reutilizados: layouts por área, botones del sistema de diseño, iconos Blade, errores de formulario, avisos, navegación, tarjetas y el controlador existente de diálogos de tienda. Se consolidó el layout de invitado usado por el componente PHP.

## 3. Inventario y cobertura

El [inventario completo](docs/audit/INVENTARIO.md) enumera **cada ruta y cada vista**, con la evidencia disponible y las limitaciones. Su [versión JSON](docs/audit/inventory.json) permite continuar sin reconstruir el mapa del proyecto.

| Área | Pantallas renderizadas o escenarios cubiertos |
|---|---|
| Público | Marketplace, landing para vender, Conócenos y documentos legales |
| Acceso | Login, registro comprador y vendedor, recuperación, restablecimiento, verificación y solicitud en revisión |
| Comprador | Catálogo, favoritos de productos/tiendas, opiniones, notificaciones, soporte FAQ/ayuda/contacto, conversación de soporte, perfil |
| Marketplace | Ficha de producto y tienda, opiniones, imágenes ausentes, textos largos, contacto y apartados opcionales |
| Comercio | Bandejas comprador/vendedor, conversación por ambos participantes, apartados por ambas áreas |
| Vendedor | Resumen, tienda nueva/edición, productos/listado/alta/edición, suscripción, herramientas con/sin plan, ofertas, novedades, estadísticas, calendario y notificaciones |
| Moderador | Resumen, publicaciones con vista previa, opiniones, reportes, vendedores, documentación, analíticas, configuración y ayuda |
| Administrador | Resumen, usuarios/listado/alta/ficha/edición, consultas/listado/hilo, solicitudes/listado/documentos, catálogo, fichas de tienda/producto, opiniones, perfil y CRUD de tipos de cuenta |

Los 71 renders se analizaron a **320, 375, 390, 430, 768, 1024, 1280, 1440 y 1920 px**: 639 comprobaciones de viewport. Se ejecutó axe con criterios A/AA de WCAG 2, 2.1 y 2.2 a 375 y 1280 px: 142 análisis. Hay capturas de referencia antes/después y revisión manual muestral de marketplace, vendedor, moderación, login y visor legal. No se afirma inspección humana de todas las capturas posibles.

En la pasada final se registraron **0 desbordamientos de documento/contenido detectados, 0 violaciones automáticas de axe y 0 excepciones JavaScript** en esas variantes. Son resultados del conjunto de fixtures y reglas ejecutadas, con las limitaciones indicadas, no una certificación de la plataforma completa.

## 4. Hallazgos y correcciones

H1–H10 corresponden a las heurísticas de Nielsen: estado del sistema, lenguaje natural, control, consistencia, prevención, reconocimiento, eficiencia, minimalismo, recuperación y ayuda.

| ID | Pantalla/componente | Descripción y causa | Categoría / gravedad | Heurística / WCAG | Corrección | Validación | Estado |
|---|---|---|---|---|---|---|---|
| NE-001 | Administración de tipos de cuenta | Cuatro acciones devolvían vistas `tipos_usuario.*` inexistentes. | Funcional, P1 | H3, H9 | Se implementaron listado paginado, alta, ficha, edición y formulario compartido con confirmación de eliminación; navegación administrativa y conteo agregado de cuentas. | `AuditRegressionTest`: renders y CRUD; conserva bloqueo de eliminación de tipos asignados. | Corregido |
| NE-002 | Eliminar cuenta | Las FK del historial comercial impiden borrar usuarios participantes; se cerraba la sesión antes de conocer el resultado. | Backend/UX, P1 | H5, H9 | Se detecta historial de chats/apartados, se explica cómo solicitar el cierre a soporte y solo se cierra sesión después de una eliminación exitosa. | Regresión específica en `ProductChatAndReservationsTest`: usuario, historial y autenticación conservados. | Corregido |
| NE-003 | Recuperar contraseña | Un fallo del transporte de correo propagaba una excepción al usuario. | Funcional, P1 | H1, H9 | Se captura el error del transporte, se registra y se conserva únicamente el correo para reintentar. | Fallo SMTP simulado en `AuditRegressionTest`, sin envío real. | Corregido |
| NE-004 | Enviar mensaje | Deshabilitar el botón no protegía el manejador de dos eventos de submit concurrentes. | Funcional, P1 | H5 | Bandera de envío en curso y estado ocupado accesible. | Playwright dispara dos submits y comprueba una sola solicitud POST. | Corregido para envíos simultáneos del mismo formulario |
| NE-005 | Chat al regresar | `pagehide` cancelaba el intervalo sin reinstalarlo al volver desde la caché de navegación. | Funcional, P2 | H1 | Se restaura el polling en `pageshow` y se evitan intervalos duplicados. | Eventos de transición de página simulados y nueva consulta observada en Playwright. | Corregido |
| NE-006 | Chat sin conexión/sesión | Solicitudes sin límite de espera y mensajes técnicos; tras 419 no había una acción visible de recuperación. | Funcional/UX, P2 | H1, H3, H9 | Timeout de 15 s, mensajes en español, borrador conservado, botón de envío bloqueado ante acceso perdido y enlace de recarga. | Playwright simula 419 y verifica borrador y estado; reglas de acceso PHP pasan. El vencimiento real de 15 s no se cronometró. | Parcialmente corregido/validado |
| NE-007 | Visores legales | Tarjetas `div` con onclick y overlays sin semántica modal, Escape ni devolución del foco; PDFs cargados inicialmente. | Accesibilidad, P1 | H3; 2.1.1, 2.4.3, 4.1.2 | Botones nativos, `dialog`, nombre accesible, iframe titulado, controlador compartido y carga al abrir; enlaces alternativos en `noscript`. | Playwright: Enter, `:modal`, Escape, foco devuelto y src diferido. Captura del diálogo abierto. | Corregido; lectura interna de los PDF pendiente |
| NE-008 | Validación compartida | Cualquier invalid abría otro modal y separaba el error del campo; los mensajes dependían del idioma del navegador. | Accesibilidad/UX, P2 | H5, H9; 3.3.1, 3.3.2, 4.1.2 | Error al lado del campo, `aria-invalid`, descripción asociada, mensajes en español y foco en el primer inválido; se limpia al corregir. | Pruebas JS y Playwright, incluidos múltiples errores y validación dentro de un diálogo. | Corregido para restricciones nativas |
| NE-009 | Formularios POST | Faltaba una barrera compartida contra doble submit nativo. | Funcional/UX, P2 | H1, H5 | Guardia delegada para POST nativo, estado ocupado y restauración al volver; conserva nombre/valor del submitter y respeta formularios AJAX. | Playwright comprueba bloqueo, payload habilitado y restauración. | Corregido en cliente; no garantiza idempotencia distribuida |
| NE-010 | Encabezado vendedor | Una regla móvil genérica posicionaba absolutamente todos los logos, superponiéndolos a volver al marketplace. | Responsive, P2 | H4, H8; 1.4.10, 2.5.8 | Se limita la regla al encabezado de marketplace y se permite envolver el encabezado vendedor. | Capturas antes/después y axe en las pantallas de vendedor. | Corregido |
| NE-011 | Publicaciones del moderador | Un `sr-only` absoluto de la tabla carecía de bloque contenedor local y extendía el documento hasta 492 px en móviles. | Responsive, P2 | H4, H8; 1.4.10 | El scroll de tabla establece su propio contexto de posición; mantiene las columnas disponibles mediante scroll local. | Antes: overflow a 320/375/390/430. Después: documento dentro del viewport y tabla desplazable. | Corregido |
| NE-012 | Tarjeta vendedor sin imagen | El enlace de imagen quedaba sin nombre cuando solo mostraba un icono. | Accesibilidad, P2 | H6; 2.4.4, 4.1.2 | Nombre accesible de edición basado en el producto, con imagen decorativa dentro del enlace. | Fixture sin imagen y axe `link-name`. | Corregido |
| NE-013 | Registro y tarjetas | Controles de mostrar contraseña de unos 22 px y enlace de tienda demasiado próximo a otras acciones. | Accesibilidad, P2 | H4, H7; 2.5.8 | Objetivo de contraseña de 44 px y altura mínima del enlace de tienda. | Axe `target-size` antes/después en registro y catálogo. | Corregido |
| NE-014 | Tablas de planes, estadísticas y moderación | Scroll horizontal sin región enfocada; faltaba semántica explícita en la tabla de publicaciones. | Accesibilidad, P2 | H7; 2.1.1, 1.3.1 | Regiones con nombre y tabindex; caption y encabezados de columna en moderación. | Axe, renders móviles y navegación con teclado muestral. | Corregido en las tablas indicadas |
| NE-015 | Horarios vacíos | `dl` contenía directamente un párrafo en el estado vacío. | Accesibilidad, P2 | H4; 1.3.1 | Se separó el estado vacío de la lista de definiciones. | Fixture de tienda sin horarios; axe `definition-list`. | Corregido |
| NE-016 | Autenticación | Recuperación/restablecimiento en inglés, título Laravel, idioma HTML en inglés y errores de acceso sin traducción; login limitaba el correo a 120 caracteres. | UX/accesibilidad, P2 | H2, H9; 3.1.1, 3.3.1 | Textos y título en español, errores asociados, enlaces de regreso, traducciones de recuperación y límite del correo de 255; layout invitado compartido. | Regresión explícita con locale `en`; render y axe. | Corregido en los flujos indicados |
| NE-017 | Landing/planes | Precios $29/$99 y prueba de 14 días no coincidían con los planes reales; enlace de ventas vacío y promesas de inventario/envíos/cifrado sin implementación encontrada. | Funcional/contenido, P1 | H2, H4, H6 | Planes derivados de `config/subscriptions.php`, copy de chat/anticipos/beneficios reales; cifras/testimonios identificados como ilustrativos. No se cambió ningún importe configurado. | `AuditRegressionTest`, enlaces y precios en Playwright. | Corregido en el contenido señalado |
| NE-018 | Landing/contraste | Texto azul sobre fondos azules mezclados daba 4.43 y 4.48:1. | Accesibilidad, P2 | H4; 1.4.3 | Variante de texto azul oscuro del sistema de tokens para nota y etiquetas. | Axe antes/después en 375/1280 px. | Corregido |
| NE-019 | Layouts base/moderador | No había atajo para omitir navegación repetida. | Accesibilidad, P2 | H7; 2.4.1 | Enlace de salto visible con foco; destino específico posterior al sidebar del moderador. | Inspección de HTML y render. Compatibilidad con lectores de pantalla reales pendiente. | Parcialmente validado |
| NE-020 | Login con poca altura | Layout fijado al viewport y overflow oculto podían cortar el formulario en escritorio horizontal de poca altura. | Responsive, P2 | H3; 1.4.10 | Contenedor en flujo normal con min-height fluida y crecimiento por contenido. | Playwright a 1024 × 375 comprueba que el botón es alcanzable mediante scroll. | Corregido |
| NE-021 | Prueba del resumen moderador | Comparaba `now()` UTC con una vista que muestra Ciudad de México, y dependía del segundo exacto. | QA, P2 | H4 | Se congela el reloj y se compara en la zona usada por la vista. | Suite completa aprobada; no se cambió la hora de almacenamiento. | Corregido |
| NE-022 | Anclas/movimiento de landing | `querySelector('#')` lanzaba una excepción al pulsar enlaces vacíos; smooth scroll y revelado JS ignoraban movimiento reducido. | JS/accesibilidad, P2 | H3; 2.3.3 como mejora adicional | Destinos reales, resolución por id y respeto de `prefers-reduced-motion` en JS. | Playwright pulsa Ver planes sin errores; los enlaces `href="#"` ya no existen. | Corregido |

No se confirmó un P0. Las correcciones P1 identificadas arriba cuentan con pruebas o evidencia concreta; las restricciones pendientes se describen a continuación.

## 5. Pendientes y limitaciones

| ID | Componente | Descripción / causa | Categoría / gravedad | Heurística | Trabajo pendiente / evidencia | Estado |
|---|---|---|---|---|---|---|
| NE-023 | Validación del resto de formularios | Algunas acciones siguen usando mensajes predeterminados de Laravel con locale `en` y solo un resumen, sin asociar cada error de servidor a su control. | UX/accesibilidad, P2 | H2, H9 | Extender la solución a todos los errores de servidor, incluidos arrays de horarios, ofertas y herramientas. No se modificó la configuración de idioma del entorno. | Parcialmente corregido |
| NE-024 | Chat/idempotencia | El envío al servidor no incorpora una clave de idempotencia del mensaje. Una respuesta perdida después de guardar podría inducir a reenviar el mismo texto. | Funcional, P2 | H5, H9 | El bloqueo cliente cubre simultaneidad; falta diseñar y probar deduplicación de reintentos sin eliminar mensajes legítimamente iguales. | Pendiente |
| NE-025 | Rendimiento | `User::tieneTipo()` consulta la relación en cada llamada y `BuyerAccountContext` carga todos los IDs favoritos. No se midió su coste con volúmenes altos. | Rendimiento, P2 | H7 | Perfilar en MySQL aislado antes de cambiar cachés o consultas; no se aplicó un caché de roles que pueda dejar permisos obsoletos. | Pendiente |
| NE-026 | Componentes/layouts heredados | `components.modal`, dropdown y varias navegaciones antiguas siguen disponibles en código, sin uso demostrado en las rutas actuales. `auth.confirm-password` referencia `password.confirm`, ruta inexistente, y tampoco tiene una ruta que exponga esa pantalla. | Arquitectura/accesibilidad, P3 | H4 | Revisar el flujo de confirmación y la semántica del modal antiguo antes de reactivarlos. No se eliminó código ni se creó un flujo de seguridad nuevo sin necesidad demostrada. Ver inventario. | Pendiente |
| NE-027 | Cobertura real | No hubo navegador conectado ni helper nativo disponible; no se probaron Safari/Firefox, lector de pantalla, teclado virtual físico, dispositivos reales ni todos los estados combinados. | QA, P2 | H1–H10 | Edge headless y pruebas PHP no sustituyen esas verificaciones. Tampoco se enviaron documentos o pagos reales. | Pendiente |
| NE-028 | Política de contraseñas | Registro exige complejidad; cambio/restablecimiento usa `Password::defaults()`. Las pruebas existentes aceptan claves de cambio sin mayúscula. | Seguridad/coherencia, P2 | H4, H5 | Definir la política antes de alterar requisitos establecidos. Se conservaron las reglas existentes. | Pendiente |
| NE-029 | Cierre administrativo/comercial | El perfil permite eliminar la propia cuenta administrativa, mientras desactivarla está prohibido. Además, el borrado de cuentas con historial comercial requiere un proceso de cierre/privacidad definido. | Gobierno de cuentas, P2 | H5, H10 | Revisar reglas para último administrador y tratamiento de historial. No se decidió una nueva política ni se eliminaron datos. | Pendiente |
| NE-030 | Landing/activos externos | Persisten mockups y ejemplos comerciales ilustrativos, fuentes/CDN y retratos de ejemplo remotos. | Contenido/rendimiento, P3 | H2, H8 | Sustituir ejemplos por contenido aprobado y recursos propios; se identificaron cifras/testimonios como ilustrativos. Pruebas con red externa bloqueada. | Parcialmente corregido |
| NE-031 | Compra completa/carrito | El código de pedidos es un esqueleto; el flujo operativo encontrado es contacto por chat y apartado opcional. | Alcance de producto, P2 | H2 | Documentar si se requiere un checkout completo como funcionalidad nueva. No se inventó ni se simuló un carrito para declarar cobertura. | Pendiente de definición |

Otras limitaciones: `BuyerDatabase::migrate()` usa una selección de migraciones reales en memoria; excluye migraciones históricas de pedidos con índices incompatibles con SQLite. No se ejecutaron migraciones contra MySQL ni se comprobó concurrencia real entre procesos. Las pruebas existentes de Mercado Pago simulan HTTP y cubren firmas, estados, importes y permisos; no comprueban la disponibilidad real del proveedor, OAuth externo ni entregas de correo. No se realizó un escaneo de vulnerabilidades de dependencias ni una prueba de penetración completa.

## 6. Accesibilidad, responsive y sistema de diseño

Se conservaron la identidad azul, los 55 tokens existentes y sus variantes semánticas. Las correcciones usan `primary-text`, colores de error, radios y sombras existentes; no introducen otra paleta. Se reutilizan layouts y diálogos. Los nuevos errores inline y estados ocupados se aplican desde módulos compartidos.

Se evaluaron con axe contraste, nombres, relaciones semánticas, regiones desplazables, tamaños de objetivo y otras reglas de las etiquetas WCAG A/AA indicadas. Se comprobaron por interacción menús, modales, foco, errores y movimiento reducido. Se registran también las reglas `incomplete` de axe que requieren revisión humana en el JSON de evidencia. Un resultado sin violaciones automáticas no garantiza conformidad completa ni compatibilidad con lectores de pantalla.

Referencia utilizada: [WCAG 2.2, guía de criterios y técnicas de W3C](https://www.w3.org/WAI/WCAG22/quickref/). El criterio 2.3.3 es una mejora adicional AAA, no un requisito de AA.

Las tablas extensas mantienen scroll local accesible; el criterio de ausencia de desbordamiento se refiere al documento y a contenido fuera de contenedores, no a prohibir el scroll necesario en una tabla. Las comprobaciones esperan dos frames tras cambiar el viewport: una primera instrumentación que no esperaba el reflow produjo falsos positivos y se descartó antes de establecer la línea base conservada.

## 7. Pruebas ejecutadas y evidencia

| Comprobación | Resultado / alcance |
|---|---|
| `php artisan test --compact` | **226 aprobadas / 2550 aserciones**, 51.87 s. Base en memoria y simulaciones existentes. |
| Regresión final `AuditCoverageTest\|AuditRegressionTest` | **5 aprobadas / 110 aserciones**, después de compilar recursos y regenerar fixtures. |
| `node --test tests/js/*.test.js` | **4 aprobadas**, geometría existente y mensajes de validación. |
| `npm.cmd run check:design` | Tokens, referencias de color y pares explícitos de contraste comprobados; no sustituye render. |
| `npm.cmd run build` | Compilación correcta, 81 módulos. Advertencia informativa de datos Browserslist antiguos. |
| `php artisan view:cache` | Todas las plantillas compilan. |
| Playwright/análisis de pantallas | Ver [resultado final](docs/audit/verified/results.json): medidas por viewport, axe y errores JS por pantalla. |
| Playwright/interacciones | **11 escenarios aprobados**. Ver [escenarios y resultados](docs/audit/verified/interactions.json). El script falla ante una aserción incumplida. |
| `git diff --check` | Sin errores de espacios al cierre. |

La suite PHP cubre autenticación/verificación, aislamiento de roles, documentos privados y MIME, propiedad de recursos, favoritos, reseñas, soporte, notificaciones, moderación, productos, tiendas, suscripciones y herramientas. En comercio incluye firmas de webhook, importes/receptor/moneda/modo, conciliaciones repetidas, fallos HTTP y privacidad de conversaciones. No implica cobertura exhaustiva de todas las ramas del código.

Evidencia conservada:

- [Resultados de referencia](docs/audit/baseline/results.json), anteriores a las correcciones, con 62 variantes.
- [Captura vendedor antes](docs/audit/baseline/23-vendedor-vendedor.dashboard-375.png) y [después](docs/audit/verified/23-vendedor-vendedor.dashboard-375.png).
- [Moderación móvil](docs/audit/verified/39-moderador-moderador.publicaciones-375.png).
- [Visor legal abierto](docs/audit/verified/dialogo-legal-375.png).
- Las carpetas `docs/audit/baseline` y `docs/audit/verified` contienen las demás capturas generadas. Todas usan datos ficticios.

Durante la ejecución inicial PHP no estaba en PATH, PowerShell impedía `npm.ps1` y el sandbox impedía a esbuild resolver carpetas superiores. Se usaron el ejecutable PHP existente de Herd y `npm.cmd`, con escalación para las acciones bloqueadas. Playwright y axe se instalaron únicamente en `storage/framework/audit-tools`, sin modificar package.json ni lockfiles; los fixtures y herramientas temporales están excluidos de Git.

## 8. Rendimiento

Se mejoró la carga inicial del contenido legal: los dos PDFs ya no se solicitan hasta abrir el visor. Se mantiene la importación diferida existente de Leaflet. Se paginó la administración de tipos de cuenta y se usa `withCount` para evitar una consulta por fila.

La compilación final informa aproximadamente: JS principal **122.32 kB / 44.07 kB gzip**, CSS de entrada principal **207.25 kB / 33.82 kB gzip**, CSS importado por JS **32.73 kB / 6.09 kB gzip**, Leaflet diferido **149.97 kB / 43.48 kB gzip**. Son tamaños de build, no tiempos de carga de usuarios.

**LCP, INP y CLS de producción no se midieron.** El modo snapshot y la red externa bloqueada no dan una medición representativa. Queda pendiente instrumentar datos de campo o Lighthouse sobre una instalación aislada comparable, con imágenes y volumen realista, antes de decidir divisiones adicionales de CSS, formatos de imagen o caché.

## 9. Archivos modificados

Backend: `app/Http/Controllers/{ProfileController,ProductChatController,TipoUsuarioController}.php`, `app/Http/Controllers/Auth/{NewPasswordController,PasswordResetLinkController}.php`, `app/Http/Requests/Auth/LoginRequest.php`, `app/View/Components/GuestLayout.php`, `lang/es/passwords.php`.

Frontend compartido: `resources/js/{app,app-dialogs,chat-commerce,form-submission}.js`; `resources/css/{design-system,local-discovery,marketplace,moderator,product-cards,site-shell}.css`; layouts `base`, `guest`, `moderator-panel` y `layouts/administrador/panel-navigation`.

Vistas: `auth/{login,forgot-password,reset-password}`, `auth/partials/seller-styles`, `chat/show`, `conocenos`, `welcome`, `marketplace/partials/business-hours`, `moderador/publicaciones`, `vendedor/{estadisticas,suscripcion}`, `vendedor/productos/card`, y cinco archivos nuevos en `tipos_usuario/`.

QA/documentación: `.gitignore`, `tests/Feature/{AuditCoverageTest,AuditRegressionTest,ModeratorPanelTest,ProductChatAndReservationsTest}.php`, `tests/js/form-validation.test.js`, `scripts/audit-{browser,interactions,inventory}.mjs`, `docs/design-system-audit.json` regenerado, este informe y `docs/audit/`.

Los recursos de `public/build` se recompilaron y ya estaban excluidos de Git. No hubo cambios en migraciones, servicios de pagos, credenciales o importes configurados.

## 10. Reproducción y siguiente trabajo

En PowerShell, usando el PHP instalado en este equipo:

```powershell
& C:/Users/Colibecas/.config/herd/bin/php84/php.exe artisan test --compact
npm.cmd run check:design
npm.cmd run build
# Exportar DESPUÉS del build, para que los HTML usen el manifiesto final.
$env:NEXOECO_AUDIT_EXPORT = '1'
& C:/Users/Colibecas/.config/herd/bin/php84/php.exe artisan test --filter='all marketplace workspaces render'
Remove-Item Env:NEXOECO_AUDIT_EXPORT
node scripts/audit-browser.mjs
node scripts/audit-browser.mjs --interactions
# Para actualizar el inventario de rutas y vistas:
& C:/Users/Colibecas/.config/herd/bin/php84/php.exe artisan route:list --json | Set-Content -Encoding utf8 storage/audit-routes.json
node scripts/audit-inventory.mjs
```

El harness requiere Edge disponible y las herramientas aisladas instaladas mediante `npm.cmd install --prefix storage/framework/audit-tools --no-save --package-lock=false playwright axe-core`. Sirve únicamente en `127.0.0.1:4179`; sus dos modos de navegador deben ejecutarse secuencialmente. Bloquea red externa y utiliza un servidor de archivos de QA, que no debe publicarse ni usarse como servidor de aplicación.

Para continuar, partir de NE-023 a NE-031 y de las filas del inventario sin ejecución demostrada. Priorizar mensajes/errores de servidor en todos los formularios, pruebas de reintento del chat con respuesta perdida, cierre de cuenta con historial y último administrador, y una instalación MySQL aislada para pruebas E2E y rendimiento. Después ampliar a lectores de pantalla, Safari/Firefox, zoom real, alto volumen y teclado virtual. No repetir los 71 renders ya cubiertos salvo cambios relacionados o regresiones.
