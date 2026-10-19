# Suscripciones de vendedores

Sección: **Mi tienda → Planes y promoción** (`/vendedor/suscripcion`).

Dos planes opcionales, mensuales, en MXN:

| Beneficio | Impulso · $149 | Pro · $299 |
| --- | --- | --- |
| Productos promocionados simultáneos | 5 | 20 |
| Prioridad en resultados que coincidan con filtros | Sobre publicaciones sin promoción | Sobre Impulso y publicaciones sin promoción |
| Tienda en recomendaciones patrocinadas | Sí | Sí, mayor prioridad |
| Cambiar productos seleccionados | Sí | Sí |
| Estadísticas de visitas, chats y apartados | Básicas, últimos 30 días | Avanzadas, por fechas y producto |
| Respuestas rápidas | 5 | Ilimitadas |
| Mensajes automáticos | 1 de bienvenida | Bienvenida y fuera de horario personalizables |
| Personalización | Color y frase de presentación | Color, frase, historia y diseño compacto |
| Productos fijados en tienda | 3 | 10 |
| Promociones comerciales informativas | 3 activas | Ilimitadas y programables |
| Novedades para seguidores | Publicación inmediata | Publicación inmediata o programada |
| Calendario y recordatorios de apartados | No | Sí |
| Reportes CSV | No | Sí |

La venta sin suscripción conserva sus funciones. Las promociones usan la etiqueta **Patrocinado**. No se vende una insignia de verificación ni se prometen ventas. Las recomendaciones rotan entre tiendas del mismo nivel. Las métricas cuentan apariciones al renderizar el catálogo, no visibilidad efectiva en pantalla, y omiten al propietario y repeticiones por sesión/elemento/hora. No guardan IPs ni identificadores personales.

## Configuración

1. Aplicar las migraciones de suscripciones, herramientas y avisos de ofertas (`2026_10_11_000000`, `2026_10_11_010000` y `2026_10_11_020000`) con `php artisan migrate`.
2. Configurar en `.env` las credenciales de **NexoEco**, que cobra las suscripciones:
   - `MERCADOPAGO_SUBSCRIPTIONS_ACCESS_TOKEN`
   - `MERCADOPAGO_SUBSCRIPTIONS_COLLECTOR_ID` (ID de la cuenta que recibe el dinero)
   - `MERCADOPAGO_SUBSCRIPTIONS_WEBHOOK_SECRET`
3. En la aplicación de Mercado Pago configurar la URL pública HTTPS `/mercadopago/suscripciones/webhook` y los eventos `subscription_preapproval`, `subscription_authorized_payment` y `payment`.
4. Configurar el scheduler de Laravel (`php artisan schedule:run` cada minuto) para reconciliar los pagos, publicar novedades programadas, avisar de ofertas programadas y enviar recordatorios privados de apartados. Los comandos `php artisan subscriptions:sync` y `php artisan seller-growth:run` también pueden ejecutarse manualmente. El primero se programa cada 15 minutos y el segundo cada minuto, ambos sin solapamiento.

Sin las tres credenciales, se muestran los planes pero se deshabilita contratar. El token nunca se envía al navegador. Las cuentas OAuth de los vendedores utilizadas para apartados son independientes.

## Ciclo de vida

El precio se toma del servidor y se conserva en cada suscripción. Los planes se configuran en `config/subscriptions.php`; un cambio de precio afecta únicamente a nuevas contrataciones.

Se crea una suscripción mensual pendiente mediante `/preapproval` y el vendedor acepta la renovación en el formulario antes de ir al checkout alojado en Mercado Pago. Ni el retorno del navegador ni el estado `authorized` conceden beneficios: se verifica una factura de la suscripción y su pago aprobado, importe, moneda y cuenta receptora. La cobertura es un mes calendario desde la fecha de débito de la factura, sin acumular tiempo por notificaciones duplicadas. Reembolsos o contracargos retiran la cobertura de esa factura al reconciliar.

La cancelación detiene futuros cargos en Mercado Pago y conserva el periodo pagado. Para cambiar de plan se cancela y espera a que termine ese periodo. Una solicitud pendiente se puede cancelar y sustituir. Las contrataciones concurrentes se serializan por vendedor; no se permite contratar otro plan mientras exista una suscripción abierta o cobertura vigente. Los productos se seleccionan individualmente, con límite aplicado en el servidor y protección de propiedad.

No se realizan cobros reales durante las pruebas. Para validar la integración completa, configurar cuentas de prueba de Mercado Pago y una URL HTTPS accesible para retorno y webhooks.

## Herramientas y funciones gratuitas

Las herramientas se configuran desde `/vendedor/herramientas`, las ofertas desde `/vendedor/promociones`, las estadísticas desde `/vendedor/estadisticas`, las novedades desde `/vendedor/novedades` y el calendario desde `/vendedor/calendario`.

El seguimiento privado de negociaciones se ofrece a **todos los vendedores** dentro del chat: Nuevo interesado, En negociación, Entrega acordada, Venta concretada y No concretada. Las notas son privadas. Al marcar Venta concretada se puede declarar un importe opcional; esos importes se muestran y exportan separados de los anticipos confirmados por Mercado Pago.

Todos los vendedores pueden gestionar solicitudes y marcar el seguimiento de entrega como pendiente, completado o cancelado. Este seguimiento nunca modifica `estado`, `monto`, `condiciones` ni los datos de pago del proveedor. Pro agrega fechas de entrega, calendario y un recordatorio privado al vendedor dentro de NexoEco.

Seguir tiendas es gratuito para compradores. Es una elección explícita, independiente de Favoritos. Se puede dejar de seguir desde la tienda o `/comprador/tiendas-seguidas`. Los nuevos productos avisan a seguidores incluso si el vendedor no tiene suscripción. Las ofertas comerciales avisan una sola vez al activarse; las programadas esperan a su inicio y requieren un plan vigente y un producto publicado. Editar una oferta o reintentar el scheduler no repite el aviso. Los avisos de ofertas, novedades y recordatorios se guardan dentro de NexoEco; no se envían correos ni mensajes externos.

## Reglas de los beneficios

- Las respuestas rápidas se insertan como **borradores** en el chat y requieren que el vendedor pulse Enviar. Se admiten las variables `{comprador}` y `{producto}`.
- La bienvenida se envía una vez al crear una conversación. Pro agrega una respuesta fuera de horario, como máximo una vez por conversación y fecha local. Los horarios nocturnos se asignan al día de apertura. Los mensajes se identifican como automáticos.
- Los productos fijados aparecen primero **dentro de la tienda**. Son independientes de los productos promocionados en búsquedas. Se aplican límites de 3 y 10 en el servidor.
- Las ofertas comerciales son informativas, con título, detalles, descuento opcional del 1 al 90% y vigencia. **No modifican el precio publicado ni el anticipo**. Impulso permite 3 activas; Pro admite más y puede programar el inicio. No se permiten ofertas simultáneas sobre el mismo producto en un periodo solapado.
- Las estadísticas básicas muestran los últimos 30 días. Pro permite periodos de hasta 366 días, detalle por producto, visitas diarias y CSV. Las visitas se registran desde la puesta en marcha; no se inventa historial. Se excluye al vendedor y se deduplican por sesión, elemento y hora. Las conversaciones se cuentan al abrir el chat, aunque no tenga mensajes, y la tasa de contacto es conversaciones iniciadas entre visitas a productos. Los guardados reflejan altas del periodo que siguen vigentes. Los apartados se agrupan por fecha de solicitud y estado actual. Las ventas declaradas se agrupan por fecha de declaración.
- Los CSV contienen datos de productos del propio vendedor, sin datos personales de compradores, y neutralizan nombres que puedan convertirse en fórmulas de hoja de cálculo.
- Las fechas de programación, horarios, calendario y filtros de estadísticas usan `America/Mexico_City`. Las horas se guardan en UTC.
- Al vencer un plan se detienen las automatizaciones y se ocultan sus ofertas, fijados y personalización. Los datos guardados se conservan; las ofertas y fijados pertenecen al periodo de suscripción. Una novedad programada cuyo plan haya vencido queda bloqueada y no se publica tarde automáticamente. Las novedades ya publicadas permanecen como historial hasta que el vendedor las retire.

La tabla completa de comparación se define en `config/subscriptions.php` y se muestra en la pantalla de planes. Los límites se aplican mediante `SellerBenefits` y se verifican en los controladores, con bloqueos por vendedor para operaciones concurrentes.

Referencias oficiales: [suscripciones pendientes](https://www.mercadopago.com.mx/developers/es/docs/subscriptions/integration-configuration/subscription-no-associated-plan/pending-payments), [API de suscripciones](https://www.mercadopago.com.mx/developers/es/reference/online-payments/subscriptions/overview), [facturas recurrentes](https://www.mercadopago.com.mx/developers/es/reference/online-payments/subscriptions/authorized-payment-search/get).
