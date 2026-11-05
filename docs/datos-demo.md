# Datos de demostración de NexoEco

Carga ejecutada el 4 de noviembre de 2026 en **MySQL local, base `nexoeco`**. Agrega registros; no vacía tablas ni modifica cuentas existentes. Los nombres, descripciones, opiniones y avisos llevan la marca DEMO. No son comercios ni operaciones reales.

## Datos agregados

| Tabla | Filas nuevas |
|---|---:|
| `users` | 1,000: 500 vendedores y 500 compradores |
| `usuario_tipo` | 1,500 asignaciones, únicamente a las cuentas DEMO |
| `tiendas` | 500 |
| `categorias` | 24, distribuidas entre los ocho departamentos existentes |
| `productos` | 50,000: 100 por tienda |
| `producto_imagenes` | 50,000: una imagen principal por producto |
| `productos_favoritos` | 1,500 |
| `tiendas_favoritas` | 500 |
| `store_follows` | 500 |
| `opiniones` | 1,500: 1,000 de producto y 500 de tienda |
| `ch_conversations` | 500 |
| `ch_conversation_participants` | 1,000 |
| `product_chats` | 500 |
| `ch_messages` | 1,500 |
| `notifications` | 1,000 avisos internos ficticios |
| `seller_quick_replies` | 1,000 |
| `seller_automations` | 500, desactivadas |
| `seller_store_designs` | 500 |
| `seller_daily_metrics` | 14,000 registros de visitas ficticias |
| `solicitudes_soporte` | 100 |
| `respuestas_soporte` | 100, mensajes de los propios compradores DEMO |
| `solicitudes_vendedor` | 100, pendientes de documentos |
| `reportes_contenido` | 50, reportes de prueba abiertos |
| **Total agregado** | **127,874 filas en 23 tablas** |

Las configuraciones de herramientas se guardan, pero no se activan beneficios de pago. No se crearon suscripciones pagadas, anticipos, pagos, cuentas de Mercado Pago, credenciales externas ni documentos oficiales. Tampoco se simularon registros operativos en sesiones, caché, colas, fallos de trabajos o bitácoras de decisiones administrativas.

## Cuentas para explorar los paneles

- Vendedor: `nexoeco.demo.seller.0001@example.test`.
- Comprador: `nexoeco.demo.buyer.0001@example.test`.
- Contraseña de las cuentas DEMO: `NexoEco-Demo-2026!`.
- Cambiar el número de `0001` a `0500` permite usar las otras cuentas de cada tipo.

Se crean verificadas para poder probar la aplicación sin enviar correos. Los vendedores DEMO tienen los roles comprador y vendedor; no se crean administradores ni moderadores ni se cambian los permisos de usuarios existentes.

Las direcciones y coordenadas son ficticias, dentro del área de Manzanillo. Los teléfonos se dejan vacíos para evitar publicar números que puedan pertenecer a personas reales. Las imágenes son **26 SVG locales reutilizables**: 24 por categoría, un logo de tienda y una portada. Están en `public/images/demo/` y no dependen de servicios externos.

## Repetir, ampliar y verificar

El seeder **`database/seeders/NexoEcoDemoSeeder.php`** crea el conjunto completo de las 23 tablas de la tabla anterior. Está registrado en `DatabaseSeeder`, por lo que ambas formas ejecutan los mismos valores: 500 tiendas, 100 productos por tienda y 500 compradores.

```powershell
# Seeder específico:
& C:/Users/Colibecas/.config/herd/bin/php84/php.exe artisan db:seed --class=NexoEcoDemoSeeder

# Seeder principal, que llama a NexoEcoDemoSeeder:
& C:/Users/Colibecas/.config/herd/bin/php84/php.exe artisan db:seed
```

Los roles comprador/vendedor y las relaciones se resuelven por nombre; no hace falta ejecutar el antiguo `TiposUsuarioSeeder` con IDs fijos. El seeder reutiliza el generador probado, las imágenes locales y el mismo bloqueo que `demo:populate`. Puede repetirse sobre los registros DEMO ya creados. No se necesita borrar la base ni ejecutar `migrate:fresh`.

Para otras cantidades, vista previa o verificación de solo lectura sigue disponible el comando parametrizable:

```powershell
# Vista previa sin escribir datos ni archivos:
& C:/Users/Colibecas/.config/herd/bin/php84/php.exe artisan demo:populate --dry-run

# Carga solicitada:
& C:/Users/Colibecas/.config/herd/bin/php84/php.exe artisan demo:populate --stores=500 --products=100 --buyers=500

# Comprobación de solo lectura:
& C:/Users/Colibecas/.config/herd/bin/php84/php.exe artisan demo:populate --stores=500 --products=100 --buyers=500 --verify
```

El comando solo funciona en entornos `local` o `testing`, con MySQL en localhost o SQLite. Comprueba tablas y columnas antes de insertar y no ejecuta migraciones. Usa un bloqueo para evitar dos cargas concurrentes y transacciones por tienda; una interrupción permite continuar ejecutando nuevamente el mismo comando.

La identificación utiliza correos reservados `nexoeco.demo.*@example.test`, categorías con sufijo `[DEMO]`, SKU `NE-DEMO-0001-0001` y UUID estables para mensajes y notificaciones. Repetir los mismos parámetros conserva los registros y no duplica la carga. No restaura ni sobrescribe modificaciones manuales de los datos DEMO. Para ampliar, usar cantidades mayores; reducir los parámetros no borra el excedente y la verificación avisará cuando el conjunto no coincida con el objetivo solicitado.

La generación usa Query Builder deliberadamente: no dispara observers, correos, automatizaciones, conciliaciones de pago ni notificaciones de productos publicados. Los avisos de muestra se insertan exclusivamente en la tabla de notificaciones, dirigidos a las cuentas DEMO.

## Verificación

- Carga inicial de MySQL completada: 500 tiendas, 50,000 productos y 50,000 imágenes principales.
- Segunda ejecución completa sobre MySQL: **0 filas nuevas**, mismos conteos verificados, 500 conversaciones y 1,500 reseñas DEMO.
- Se comprueba el conteo por tienda, las categorías, la imagen principal de cada producto y que los apartados no estén activados.
- `DemoMarketplacePopulationTest` comprueba simulación sin escrituras, relaciones, idempotencia, protección de cuentas existentes, bloqueo en producción y ausencia de efectos externos; usa SQLite en memoria.
- La suite de regresión usa su base en memoria y conserva los datos de MySQL.
- Suite completa: **230 pruebas aprobadas, 2,748 aserciones**. Las comprobaciones adicionales de render de cuentas DEMO se ejecutan también con la prueba específica.
- Validación del seeder Laravel y del generador: **7 pruebas aprobadas, 262 aserciones**, sobre SQLite en memoria. Incluye ejecución repetida, cantidades predeterminadas de `db:seed` y rechazo de producción.

Código: `database/seeders/NexoEcoDemoSeeder.php`, `database/seeders/DatabaseSeeder.php`, `app/Console/Commands/PopulateDemoMarketplace.php`, `app/Services/DemoMarketplacePopulator.php` y `app/Support/DemoCatalog.php`.
