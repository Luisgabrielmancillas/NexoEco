# Estados de suscripción y recuperación de sesión

## Cómo se habilita un plan

La fuente del plan es `seller_subscriptions`, mediante `User::sellerSubscriptions()`. `SellerSubscription::scopeEntitled()` filtra las consultas y `SellerSubscription::entitled()` comprueba un registro. Ambos exigen:

1. `plan` igual a `impulso` o `pro`.
2. `status` igual a `authorized`, `paused` o `cancelled`.
3. `paid_until` estrictamente posterior a la fecha actual; `NULL` o una fecha vencida no conceden acceso.

`SellerBenefits::subscription()` exige además una cuenta activa con rol vendedor y selecciona el registro vigente más reciente. `require($user, true)` exige que su plan sea `pro`. Las rutas de vendedor requieren autenticación y correo verificado. Las vistas de herramientas, estadísticas, calendario, chat y navegación consultan esos beneficios. No hace falta cerrar y abrir sesión para actualizar el plan: se consulta en cada petición.

`cancelled_at` registra la cancelación de renovación; no bloquea por sí mismo el tiempo ya cubierto. Por eso un registro cancelado o pausado conserva beneficios hasta su vencimiento. La sincronización consulta Mercado Pago, comprueba sus facturas y pagos, y actualiza la cobertura; los reembolsos retiran la cobertura correspondiente.

## Valores de status

La columna es `VARCHAR(30)`, con valor predeterminado `pending`; la base de datos no impone un enum y permite almacenar textos inválidos. Los estados reconocidos por el código se centralizan en `SellerSubscription::STATUSES`:

| Estado | Interpretación |
| --- | --- |
| `pending` | Solicitud pendiente; sin beneficios |
| `authorized` | Mandato autorizado; beneficios únicamente con cobertura futura |
| `paused` | Mandato pausado; conserva cobertura futura |
| `cancelled` | Renovación cancelada; conserva cobertura futura |

`approved` es un estado de **pago** que verifica `MercadoPagoSubscriptions::sync()`. No es un estado del mandato de suscripción y no habilita beneficios al almacenarlo en `seller_subscriptions.status`. `active` tampoco es un estado reconocido. Las pruebas comprueban que esos valores no concedan beneficios incluso con fecha futura. No se amplía la lista de estados para aceptar pagos sin verificar.

## Otras tablas

- `usuario_tipo` y `tipos_usuario`: roles del usuario, no nivel de suscripción.
- `product_promotions`: selección de productos que se promocionan con una suscripción.
- `product_pins`: productos fijados dentro de la tienda.
- `promotion_metrics` y `seller_daily_metrics`: métricas.
- `seller_store_designs`, `seller_quick_replies`, `seller_automations`, `commercial_offers` y `store_updates`: configuración y contenido de herramientas.
- `mercado_pago_accounts`: conexión del vendedor para apartados; no controla su plan Pro.

Ninguna de esas tablas contiene una segunda activación del plan. Activar Pro no selecciona automáticamente productos para promoción: el vendedor los elige desde Planes y promoción. Los precios y límites se definen en `config/subscriptions.php`.

## Usuario 10: intervención autorizada

El registro encontrado el 26 de octubre de 2026 tenía `plan=pro`, `status=active`, importe `299.00 MXN`, `paid_until=2026-12-31 11:44:28` y `cancelled_at=NULL`. La cuenta estaba activa, con correo verificado, rol vendedor y una tienda. No tenía `provider_id`.

A petición del propietario del proyecto se normalizó **únicamente el estado a `authorized`** dentro de una transacción, conservando importe, vigencia y demás datos. Se verificó después que `entitled()` devolviera `true` y que `SellerBenefits::require($user, true)` resolviera esa suscripción. La operación se registró en el log de Laravel. No fue necesario modificar roles ni otra tabla.

Es una habilitación manual local, sin cobros ni comprobación de un pago real en Mercado Pago. No tiene mandato remoto ni renovación automática asociada. La aplicación utiliza UTC: la vigencia indicada corresponde a esa zona horaria. El acceso termina cuando vence el periodo; la comprobación se hace en cada petición.

## Error 419 al salir

Los formularios de salida incluyen `@csrf`. Laravel genera 419 cuando el token enviado no coincide con el de la sesión actual, por ejemplo tras vencer la sesión o usar una pestaña anterior a otro inicio/cierre de sesión. No se encontró un token ausente en esos formularios. La configuración local usa sesiones en base de datos, duración de 120 minutos y cookies con SameSite=Lax. No se pudo reproducir la navegación exacta del usuario en un navegador.

Se añadió recuperación de errores HTML 419 con un modal de NexoEco:

- Si el intento de cierre ya corresponde a una sesión no autenticada, se vuelve al inicio.
- Si la cuenta continúa autenticada, no se ejecuta la solicitud con token inválido: el modal solicita confirmar el cierre con el token actual.
- Otros formularios vencidos ofrecen volver al inicio; no se reenvían operaciones automáticamente.
- Las respuestas JSON conservan su estado de error 419. La protección CSRF permanece activa y las respuestas de recuperación se marcan `no-store, private`.
- La recuperación se devuelve dentro del middleware CSRF para que `StartSession` pueda guardar la sesión y emitir su cookie antes de responder, incluso si se recuperó la identidad con una cookie de «recordarme».

Las pruebas de recuperación fuerzan CSRF real (Laravel normalmente lo omite en pruebas) y comprueban tokens inválidos, confirmación válida, salida repetida, ausencia de mutaciones y respuestas JSON.

## Resultado de verificación

- Suite completa: **219 pruebas aprobadas y 2,399 comprobaciones**.
- Compilación Vite y compilación de vistas Blade correctas.
- Auditoría de estilos: 130 plantillas, 163 archivos de interfaz, 55 tokens, 21 combinaciones de contraste y 208 pares explícitos de texto/fondo, sin errores.
- Sintaxis del JavaScript de diálogos y `git diff --check` correctos.
- El script temporal que consultó y normalizó el registro local se retiró después de verificar el acceso. No se modificaron secretos ni configuración de producción.
- Sin comprobación visual en navegador ni transacciones reales de Mercado Pago.
