# Carruseles de tiendas

Las secciones **Tiendas cerca de ti** y **Tiendas locales** del inicio usan una sola fila horizontal. Se muestran 3 tarjetas cercanas y 6 tiendas locales en escritorio amplio; la cantidad visible se adapta hasta 1 cercana o 2 locales en móvil.

- Avance automático de una tarjeta cada **5 segundos**, con retorno al inicio al terminar.
- Sin selector de flechas, contador o botón Pausar, y sin barras de desplazamiento visibles en ninguno de los dos carruseles.
- Desplazamiento táctil y navegación del contenedor con flechas, Inicio y Fin.
- La rotación se detiene al pasar el cursor, enfocar las tarjetas, arrastrar, ocultar la pestaña o salir del viewport. Al terminar la interacción, puede continuar automáticamente. Con el contenedor enfocado, Espacio permite pausar/reanudar de forma persistente.
- Con `prefers-reduced-motion`, comienza pausado y los cambios manuales no usan desplazamiento animado.
- Si todas las tarjetas caben, no se inicia el temporizador. Sin JavaScript, la fila sigue pudiendo desplazarse y Ver todas funciona.

**Ver todas** abre `/comprador/tiendas`: listado con búsqueda, filtro por categoría, selector Todas/Cerca de mí y paginación de 24 tiendas. La vista cercana usa la ubicación guardada de la cuenta y ordena todos los resultados del radio configurado por distancia; no queda limitada a las 12 tarjetas de la vista previa. Los filtros se conservan entre páginas y al regresar al inicio. El acceso requiere sesión y correo verificado, mediante el grupo comprador existente.

El inicio carga como máximo 12 cercanas y 24 locales para los carruseles. La paginación de tiendas desaparece del inicio y permanece en el directorio. La paginación del catálogo se reutiliza en español con controles nativos y números de página.

## Verificación

- Regresión Laravel: **234 pruebas aprobadas, 2785 aserciones**.
- Pruebas finales específicas: **4 aprobadas, 31 aserciones**; orden por distancia, filtros, más de 24 tiendas, estados vacíos y autorización.
- Playwright/Edge: **7 escenarios aprobados**, incluidos cinco segundos reales de avance, pausa, teclado, navegación de Ver todas y movimiento reducido.
- Inicio y dos directorios a **320, 375, 390, 430, 768, 1024, 1280, 1440 y 1920 px**, sin desbordamiento del documento y con tarjetas del carrusel en una fila.
- **6 auditorías axe sin violaciones automáticas** y sin excepciones JavaScript en las pantallas probadas. No equivale a una certificación WCAG completa.
- `npm.cmd run build`, `npm.cmd run check:design` y `git diff --check` correctos.

Las pruebas de navegador usan HTML renderizado por Laravel con datos ficticios en SQLite en memoria y los recursos compilados. Evidencia en [results.json](carousel/results.json), [escritorio](carousel/inicio-1440.png) y [móvil](carousel/inicio-375.png).

Para repetir:

```powershell
npm.cmd run build
$env:NEXOECO_CAROUSEL_EXPORT = '1'
& C:/Users/Colibecas/.config/herd/bin/php84/php.exe artisan test --filter=StoreCarouselTest
Remove-Item Env:NEXOECO_CAROUSEL_EXPORT
node scripts/test-store-carousel.mjs
```

El script usa Playwright y axe instalados previamente en `storage/framework/audit-tools`, y sirve únicamente en loopback `127.0.0.1:4180`. No modifica los datos de MySQL ni envía solicitudes a servicios externos.
