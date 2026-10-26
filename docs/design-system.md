# NexoEco: identidad visual azul

Fecha: 26 de octubre de 2026.

La implementación utiliza la arquitectura existente: Laravel 12, Blade, Tailwind CSS 3, Alpine.js, Vite y Leaflet. No incorpora dependencias, rutas, migraciones, permisos ni cambios en pagos o suscripciones. Conserva los cambios de favoritos, registro y moderación que ya estaban en el espacio de trabajo.

## Auditoría y cobertura

Se leyó el código de todas las plantillas, hojas de estilo y módulos JavaScript de `resources`, además de la configuración de Tailwind, Vite, Chatify y los recursos gráficos de `public`. La auditoría reproducible incluye 158 archivos de interfaz y 129 plantillas, incluida la nueva versión compartida del logo vectorial. Las vistas sin colores locales heredan el sistema desde sus layouts.

| Área | Cobertura de código |
| --- | --- |
| Pública | Landing, Conócenos, marketplace, buscador, categorías, productos, tiendas, opiniones, reportes, ubicación y rutas en mapas |
| Acceso | Inicio de sesión, registro de compradores y vendedores, verificación, recuperación y confirmación de contraseña |
| Comprador | Inicio, favoritos, tiendas seguidas, opiniones, notificaciones, soporte, perfil, conversaciones y apartados |
| Vendedor | Resumen, tienda, productos, ofertas, suscripciones, herramientas, estadísticas, seguidores, calendario, conversaciones y apartados |
| Administración | Resumen, usuarios, catálogo, opiniones, solicitudes de vendedores y soporte |
| Moderación | Resumen, publicaciones, vendedores, opiniones, reportes, analíticas, ayuda, configuración y confirmación de eliminación |
| Compartidas | Layouts actuales y heredados, formularios, botones, navegación, dropdowns, modales, paginación, notificaciones y estados vacíos |
| Correo | Verificación de cuenta y aviso de documentos para moderación |

El [inventario completo](design-system-audit.json) enumera cada archivo revisado. No había plantillas propias de errores ni componentes de skeleton en el proyecto; no se crearon interfaces nuevas para ellos. Las páginas de excepción proporcionadas por Laravel y los estilos de dependencias no se modificaron.

## Fuente única de colores

`resources/design-system/tokens.json` contiene los diez tonos Primary 50–900 indicados y los neutros solicitados. Define 55 tokens, incluidos alias semánticos y variantes accesibles para estados.

| Uso | Token / valor |
| --- | --- |
| Acción principal | `--color-primary`: `#2563EB` |
| Hover / pulsado | `--color-primary-hover`: `#1D4ED8`; `--color-primary-active`: `#1E40AF` |
| Selección / fondo de acento | `--color-primary-soft`: `#EFF6FF`; `--color-primary-subtle`: `#DBEAFE` |
| Fondo / secundario / superficie | `#F8FAFC` / `#F1F5F9` / `#FFFFFF` |
| Bordes / contornos de controles | `#E2E8F0` / `#64748B` |
| Texto principal / secundario / auxiliar | `#0F172A` / `#475569` / `#64748B` |
| Éxito / advertencia / error / información | `#16A34A` / `#D97706` / `#DC2626` / `#0284C7` |

Los tonos de estado tienen variantes `-text`, `-soft` y `-border`. Los verdes y ámbares base sirven como acentos; el texto pequeño usa sus variantes oscuras para conservar contraste. El texto auxiliar se reserva para superficies donde cumple el contraste; sobre fondos teñidos se utiliza el secundario.

`scripts/build-design-tokens.mjs` genera `resources/css/design-tokens.css` y el favicon SVG. Se ejecuta antes de `npm run build` y `npm run dev`. Los alias existentes `--nexo-*` apuntan a los tokens nuevos.

Tailwind consume los mismos tokens mediante canales RGB, incluidos modificadores de opacidad. Los mapas usan `resources/js/design-tokens.js`, que devuelve colores concretos compatibles con Leaflet. Los correos usan `App\Support\DesignTokens`, que resuelve los mismos colores del JSON y emite valores inline compatibles con clientes de correo.

Para cambiar la paleta, editar el JSON y ejecutar `npm run tokens`. Si el servidor de desarrollo ya está abierto, regenerar los tokens o reiniciarlo. No editar manualmente los archivos generados.

## Componentes y jerarquía

- Navegación blanca, texto neutro y selección azul suave. El sidebar del moderador deja de ser un bloque de color intenso.
- Tarjetas y áreas de lectura blancas o neutras. El plan Pro usa borde azul, texto oscuro y detalles azules suaves.
- Botones principales, enlaces destacados, controles seleccionados, progreso y mapas comparten el acento azul.
- Formularios blancos con bordes visibles, foco azul y validaciones rojas o verdes. Los avisos distinguen información, éxito, advertencia y error.
- Mensajes propios en azul suave y entrantes en neutro; se conservan autor, posición, hora, lectura y estados de envío existentes.
- Gráficas generales azules; las series de aprobación y rechazo conservan semántica verde/roja, etiquetas y valores.
- Botones compartidos con estados normal, hover, pulsado, foco y deshabilitado. Se mantienen indicadores y textos de carga existentes; `aria-busy` conserva el cursor de progreso.
- La selección de navegación también usa peso tipográfico e indicadores de borde. El foco incluye un contorno azul y un anillo blanco para fondos oscuros. Se respeta `prefers-reduced-motion` globalmente.
- Se conservan grids, dimensiones y breakpoints existentes. No se ha medido el layout en un navegador.

## Recursos gráficos

Los tres SVG duplicados de navegación se consolidaron en `navigation-logo.blade.php` conservando su geometría. El logo PNG transparente original se presenta con una máscara CSS azul exclusiva del componente de marca; el archivo y su silueta se conservan. En navegadores sin soporte para máscaras se muestra el recurso original. No se aplican máscaras ni filtros a imágenes de productos, tiendas o usuarios.

Los dos iconos PNG de tiendas son monocromos y permanecen intactos. El favicon ICO existente estaba vacío; ahora los documentos de la aplicación enlazan un favicon SVG generado a partir del isotipo vectorial y la paleta central. Los documentos legales PDF no se modificaron. No quedan recursos naranjas de marca que requieran rediseño manual entre los recursos gráficos revisados.

## Tonos cálidos conservados

No quedan literales HEX/RGB/HSL de identidad naranja en CSS, estilos inline, SVG de componentes o JavaScript de la interfaz. Los colores concretos se concentran en el JSON y sus artefactos generados. Los números de pedido `#1041` y `#1042` de la landing son contenido, no colores.

| Token | Valor | Clasificación y motivo |
| --- | --- | --- |
| `warning` | `#D97706` | Estado funcional de advertencia |
| `warning-text` | `#92400E` | Texto accesible para advertencias |
| `warning-soft` | `#FFFBEB` | Fondo semántico de advertencia |
| `warning-border` | `#FDE68A` | Borde semántico de advertencia |
| `store-terracotta` | `#A34D2C` | Personalización de tienda elegida por el vendedor |
| `store-terracotta-soft` | `#FFF0E5` | Fondo de esa personalización |

Terracota, Bosque y Océano siguen disponibles como elecciones de los vendedores. Las tiendas sin un diseño guardado utilizan la presentación azul de marca. No se reescribieron diseños guardados ni datos de usuarios. Los botones de acción mantienen el azul principal.

## Verificación

| Comprobación | Resultado |
| --- | --- |
| `npm run build` | Correcto; 77 módulos transformados |
| `php artisan view:cache` | Todas las plantillas Blade compilaron |
| Sintaxis JavaScript | 18 archivos comprobados con `node --check` |
| `node --test tests/js/discovery-geo.test.js` | 2 pruebas aprobadas |
| `php artisan test --compact` | 193 aprobadas; los mismos 13 fallos previos en `SellerGrowthToolsTest` |
| `npm run check:design` | 21 combinaciones de tokens y 195 pares explícitos de texto/fondo cumplen los umbrales comprobados; sin colores de estilo hardcodeados ni variables CSS sin definir |

Blanco sobre el botón principal tiene contraste **5.17:1** y sobre hover **6.70:1**. El borde de controles sobre blanco tiene **4.76:1**. Las variantes de texto de éxito, advertencia, error e información superan **4.5:1** sobre sus fondos suaves.

Los 13 fallos de Laravel se deben al alias `check.suscripcion`, usado en `routes/web.php` sin registro en `bootstrap/app.php`. Ya existían antes del rediseño y esos archivos permanecen intactos. No se alteró la protección de suscripciones para resolverlos. Vite también informa que la base local de Browserslist está desactualizada; no impide la compilación y no se actualizaron dependencias.

**Pendiente de verificación visual:** no había navegadores conectados y el navegador integrado no estaba disponible. No se realizaron capturas, mediciones de desbordamiento ni pruebas táctiles o de teclado en un navegador. Revisar teléfonos, tablets, laptops y escritorio, además de contraste heredado, transparencias y contenido real. Las comprobaciones de contraste son estáticas; no constituyen una certificación global WCAG 2.2 AA del renderizado final.

## Archivos de esta implementación

La siguiente lista corresponde al rediseño. Los cambios funcionales anteriores del espacio de trabajo se conservaron.

- `app/Support/DesignTokens.php`
- `docs/design-system-audit.json`
- `docs/design-system.md`
- `package.json`
- `public/favicon.svg`
- `resources/css/admin.css`
- `resources/css/app.css`
- `resources/css/buyer.css`
- `resources/css/chat-commerce.css`
- `resources/css/design-system.css`
- `resources/css/design-tokens.css`
- `resources/css/local-discovery.css`
- `resources/css/marketplace.css`
- `resources/css/moderator.css`
- `resources/css/product-detail.css`
- `resources/css/seller-experience.css`
- `resources/css/seller-growth.css`
- `resources/css/seller.css`
- `resources/css/site-shell.css`
- `resources/css/subscriptions.css`
- `resources/design-system/tokens.json`
- `resources/js/design-tokens.js`
- `resources/js/local-discovery.js`
- `resources/js/location-map.js`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/partials/seller-styles.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/components/application-logo.blade.php`
- `resources/views/components/auth-session-status.blade.php`
- `resources/views/components/danger-button.blade.php`
- `resources/views/components/input-error.blade.php`
- `resources/views/components/nav-link.blade.php`
- `resources/views/components/navigation-logo.blade.php`
- `resources/views/components/primary-button.blade.php`
- `resources/views/components/responsive-nav-link.blade.php`
- `resources/views/components/secondary-button.blade.php`
- `resources/views/components/seller-growth-hero.blade.php`
- `resources/views/components/text-input.blade.php`
- `resources/views/conocenos.blade.php`
- `resources/views/emails/seller-documents-ready.blade.php`
- `resources/views/emails/verify-email.blade.php`
- `resources/views/layouts/administrador/app.blade.php`
- `resources/views/layouts/administrador/guest.blade.php`
- `resources/views/layouts/administrador/navigation.blade.php`
- `resources/views/layouts/base.blade.php`
- `resources/views/layouts/comprador/guest.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/layouts/moderador/app.blade.php`
- `resources/views/layouts/moderador/guest.blade.php`
- `resources/views/layouts/moderador/navigation.blade.php`
- `resources/views/layouts/vendedor/app.blade.php`
- `resources/views/layouts/vendedor/guest.blade.php`
- `resources/views/layouts/vendedor/navigation.blade.php`
- `resources/views/marketplace/solicitud-vendedor.blade.php`
- `resources/views/marketplace/tienda.blade.php`
- `resources/views/moderador/dashboard.blade.php`
- `resources/views/welcome.blade.php`
- `scripts/build-design-tokens.mjs`
- `scripts/check-design-system.mjs`
- `tailwind.config.cjs`
- `tests/Feature/SellerRegistrationTest.php`
- `vite.config.js`
