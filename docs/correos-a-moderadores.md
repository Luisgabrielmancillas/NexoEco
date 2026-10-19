# Avisos de documentación a moderadores

Cuando un vendedor guarda un registro con documentos o reenvía correcciones, NexoEco envía un correo a cada cuenta activa con rol **moderador** y correo verificado. El aviso se envía después de confirmar la transacción. No se envía por un formulario inválido, una solicitud duplicada que ya está en revisión ni un fallo al guardar los archivos.

El correo contiene el nombre del solicitante, número y fecha de solicitud y un botón **Revisar solicitud** que abre su expediente protegido. No adjunta identificación, constancia fiscal ni comprobantes, y no incluye RFC, CURP ni domicilio fiscal.

Se usa el SMTP ya configurado en `.env`, el mismo servicio de los códigos de verificación. No hay una conexión adicional. Para entregar correo real deben ser válidos `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` y la configuración de transporte de `config/mail.php`. Con `MAIL_MAILER=log` solo se escribe el correo en el log. `APP_URL` debe ser el dominio accesible del sitio para que el botón del correo funcione fuera del equipo local.

El primer envío se intenta al terminar de guardar la solicitud. Si SMTP falla, se reporta el error y se guarda un trabajo en la cola `database`, con una espera inicial de un minuto y hasta tres intentos. Los otros moderadores siguen recibiendo sus avisos. Los reintentos necesitan el worker:

```sh
php artisan queue:work database --tries=3 --timeout=60
```

El comando `composer run dev` del proyecto ya inicia un listener de colas. En producción mantén el worker supervisado y reinícialo después de desplegar cambios. Comprueba fallos con `php artisan queue:failed` y reinténtalos con `php artisan queue:retry ID` tras corregir el SMTP. Este comportamiento usa [colas y transacciones de Laravel](https://laravel.com/docs/12.x/queues#jobs-and-database-transactions) y [notificaciones de correo](https://laravel.com/docs/12.x/notifications#mail-notifications).

El worker omite avisos de solicitudes ya revisadas, envíos anteriores reemplazados por otra documentación y moderadores desactivados o sin permiso. Un fallo de correo no elimina la cuenta ni los documentos ya guardados.

Las pruebas simulan el envío para verificar destinatarios, reenvíos, transacciones y errores, sin enviar correos a personas reales.
