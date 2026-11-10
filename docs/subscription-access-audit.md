# Auditoría de acceso a planes y mensajes de interfaz

## Fallos corregidos

- La navegación de vendedor consultaba `User::subscription`, una relación inexistente. Ahora utiliza `SellerBenefits`, igual que las herramientas y las estadísticas.
- El grupo de herramientas y estadísticas utilizaba un alias de middleware no registrado. Las pantallas conservan su vista previa gratuita y cada operación de pago sigue validándose en el controlador con `SellerBenefits`. La eliminación de respuestas propias sigue disponible después del vencimiento. El alias también queda registrado y su middleware utiliza la validación central para los casos que sí requieran restringir una pantalla completa.
- Un registro histórico del plan `gratis`, autorizado por diez años, podía contarse como una suscripción pagada y bloquear la contratación de Pro. Los scopes y la comprobación de cobertura ahora solo admiten Impulso y Pro. La contratación y la pantalla de planes ignoran esos registros gratuitos. Básico es el nivel predeterminado: no crea mandatos ni necesita credenciales de pago. No se eliminan registros existentes.
- El checkout rechaza el plan gratuito; únicamente los planes de pago pueden enviarse a Mercado Pago.
- Los enlaces bloqueados usaban `alert()`. Ahora abren un diálogo de la aplicación con explicación, cierre y acceso a «Ver mi plan». No se sustituyen las funciones globales del navegador.
- La validación de formularios intercepta el evento `invalid` y presenta el primer error en el mismo diálogo, conservando las restricciones, los mensajes de validación y el regreso del foco al campo. Se retiran las llamadas a `reportValidity()` del registro y de moderación. Las confirmaciones existentes de acciones destructivas se conservan.
- Los botones de exploración permiten saltos entre palabras completas. Sus textos tienen un contenedor separado del icono y los iconos no se encogen. Los grupos conservan las alturas uniformes.

## Reglas conservadas

La autorización de la suscripción no basta para habilitar beneficios. Se mantiene la verificación de factura y pago aprobado, importe, moneda, cuenta receptora y ausencia de reembolso. El periodo debe seguir vigente. Cancelar la renovación o pausar el mandato conserva el periodo ya pagado; al vencer, los beneficios se bloquean. No se cambian precios, rutas, permisos ni tablas.

## Verificación

- Pruebas existentes y nuevas sobre navegación y acceso Pro tras sincronizar un pago verificado, rechazo de pagos pendientes, vencimiento, cancelación, pausa, registros gratuitos históricos y checkout Pro.
- Suite completa de Laravel: **211 pruebas aprobadas, 2,344 comprobaciones**. Compilación Vite y compilación de vistas Blade correctas. Auditoría de 55 tokens, 21 combinaciones de contraste y 208 pares explícitos de texto/fondo sin errores. Comprobación de sintaxis de los módulos JavaScript modificados y 2 pruebas JavaScript existentes aprobadas.
- Búsqueda de `alert()`, `confirm()`, `prompt()` y `reportValidity()` en el código de interfaz.

Las pruebas automatizadas utilizan respuestas simuladas de Mercado Pago y no ejecutan cobros reales. La activación manual posterior del usuario 10 está documentada en [la auditoría de estados y sesión](subscription-status-and-session-audit.md). La comprobación visual en navegador y una transacción de prueba con webhooks reales requieren un navegador y un entorno de pagos disponibles.
