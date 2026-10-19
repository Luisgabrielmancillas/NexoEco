# Chat y apartados con Mercado Pago

Las áreas de compra y venta están separadas incluso cuando una persona tiene ambos roles:

- `/mensajes` y `/apartados` muestran solo conversaciones y anticipos en los que la persona es compradora, con el diseño del marketplace.
- `/vendedor/mensajes` y `/vendedor/apartados` muestran solo consultas y anticipos sobre sus productos, dentro de Mi tienda.
- Mi tienda presenta Resumen, Productos, Mensajes y Apartados recibidos, en ese orden. El menú del marketplace agrupa esos accesos dentro del desplegable Mi tienda.
- Al abrir una conversación, el diseño corresponde al papel de esa persona en esa conversación. Tener rol vendedor no cambia una compra al área de venta.

Chatify 2.0.0-beta.2 mantiene conversaciones privadas por producto/comprador, historial y mensajes sin leer. La interfaz se actualiza cada tres segundos sin Pusher ni colas. El botón de chat está disponible tanto con apartados como sin ellos.

## Configuración de Mercado Pago (una vez para la plataforma)

1. Crea una aplicación Checkout Pro con integración para marketplace en [Tus integraciones](https://www.mercadopago.com.mx/developers/panel/app). Obtén el ID de aplicación y su Client Secret.
2. Configura `APP_URL` con el dominio HTTPS público del sitio. Registra como URL de redirección exacta `https://TU-DOMINIO/vendedor/mercadopago/retorno`. Habilita **Authorization Code con PKCE** en los detalles de la aplicación.
3. Registra el webhook `https://TU-DOMINIO/mercadopago/webhook`, selecciona **Pagos** y copia la clave secreta para validar su firma. La preferencia también incluye esta URL con la cuenta correspondiente.
4. Completa el `.env` y ejecuta `php artisan config:clear`:

```dotenv
APP_URL=https://TU-DOMINIO
MERCADOPAGO_CLIENT_ID=ID_DE_APLICACION
MERCADOPAGO_CLIENT_SECRET=CLIENT_SECRET
MERCADOPAGO_WEBHOOK_SECRET=SECRETO_DE_WEBHOOK
MERCADOPAGO_SANDBOX=true
```

No necesitas Public Key ni SDK de frontend para este checkout por redirección. Cada vendedor abre **Mi tienda → Apartados recibidos → Conectar Mercado Pago**, autoriza su cuenta y vuelve al sitio. No introduce tokens ni IDs en cada producto. La app conserva los tokens cifrados, comprueba `state` y PKCE y renueva el acceso cuando vence. No cambies `APP_KEY` sin un proceso de recifrado: los tokens se cifran con esa clave.

Mercado Pago requiere el token OAuth de cada vendedor para recibir los fondos en su cuenta; [documentación de marketplace](https://www.mercadopago.com.mx/developers/es/docs/checkout-pro-preferences/how-tos/integrate-marketplace) y [OAuth](https://www.mercadopago.com.mx/developers/es/docs/security/oauth/creation). Un token global de la plataforma depositaría los fondos en la plataforma. Se utiliza `marketplace_fee=0`; Mercado Pago puede cobrar sus propias comisiones al vendedor.

## Prueba y activación

Usa las cuentas de prueba de Mercado Pago México, vendedores y compradores distintos, y los medios de pago de prueba de Checkout Pro. Con `MERCADOPAGO_SANDBOX=true`, OAuth solicita un token de prueba y el checkout utiliza `sandbox_init_point`. La cuenta vinculada debe corresponder al mismo entorno. Prueba un anticipo aprobado, uno pendiente y otro rechazado; verifica importe, receptor y el cambio de estado desde ambos paneles. Comprueba también un reembolso y la llegada del webhook.

Para pagos reales establece `MERCADOPAGO_SANDBOX=false`, verifica las credenciales de producción y vuelve a vincular las cuentas en ese entorno. Mantén bases de datos separadas para pruebas y producción. Mercado Pago necesita un dominio HTTPS público para retornos y notificaciones; `nexoeco.test` local requiere un túnel HTTPS con la misma URL registrada de OAuth.

## Comportamiento

El vendedor activa apartados en crear/editar producto y define anticipo menor que el precio y condiciones de entrega, plazo y reembolso. Necesita tener conectada su cuenta. Al desactivar la opción, desaparece la sección de apartados de la ficha. El chat siempre continúa disponible.

El comprador acepta las condiciones y sale a Checkout Pro para pagar solo el anticipo. Al volver, la aplicación consulta el pago desde el servidor y valida referencia, monto, moneda, vendedor y entorno. El estado de la URL de retorno nunca se toma como prueba del pago. Los webhooks verifican HMAC-SHA256 antes de consultar el recurso y actualizar el registro; [documentación de webhooks](https://www.mercadopago.com.mx/developers/es/docs/checkout-pro-preferences/additional-content/notifications/webhooks).

Los reintentos reutilizan el apartado y su preferencia; antes de volver al checkout se consulta si hay un pago realizado o pendiente. La bandeja conserva la copia de las condiciones aceptadas, aunque el vendedor edite el producto. Si hay un pago adicional inesperado sobre una preferencia ya pagada, se registra en el log para revisión del vendedor y no sustituye el recibo inicial. No hay carrito ni cobro del precio completo ni bloqueo automático de inventario. El vendedor acuerda el resto y la entrega por chat y gestiona devoluciones desde Mercado Pago.

Se retiró el flujo activo de PayPal. Las columnas históricas se conservan para no eliminar registros de apartados previos, que siguen visibles en el historial.

```sh
composer install
php artisan migrate
php artisan optimize:clear
npm install
npm run build
php artisan test
```

La migración de Chatify incluida evita borrar tablas existentes. No ejecutes `chatify:install` ni republiques sus migraciones con `--force`. Las pruebas automatizadas simulan Mercado Pago; los pagos reales requieren completar la vinculación y una prueba integral con el proveedor.
