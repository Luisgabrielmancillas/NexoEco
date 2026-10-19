# Notificaciones, ubicación y rutas

La campana funciona en el marketplace y en Mi tienda. Guarda todos los avisos en la base de datos y revisa novedades cada ocho segundos mientras la página está visible. Un aviso nuevo muestra un enlace al detalle. Las respuestas de soporte y las novedades de cuenta siguen usando la misma bandeja.

Cada mensaje del chat de producto notifica únicamente al otro participante. Leer los mensajes marca como leídos sus avisos correspondientes, sin afectar otros chats. La creación de un apartado avisa al vendedor como solicitud pendiente; la confirmación del anticipo y los cambios de estado avisan al comprador y al vendedor. Repetir una confirmación con el mismo estado no duplica avisos. Estas notificaciones son internas; no requieren correo, Pusher ni permisos de notificaciones del navegador.

La portada muestra hasta seis tiendas a diez kilómetros del punto guardado, ordenadas por distancia en línea recta. Solo se consideran vendedores activos y autorizados con coordenadas. Las ubicaciones que tienen únicamente dirección escrita ofrecen agregar un punto en el mapa: no se inventan distancias a partir del nombre de la ciudad. Cambiar la ubicación actualiza las recomendaciones, distancias y origen de las rutas después de guardar.

La comparación con el dispositivo ofrece cambiar la ubicación cuando la separación supera cinco kilómetros, descontando la incertidumbre del GPS. Se comprueba automáticamente solo si el navegador ya tiene permiso; en otros casos se usa el botón **Comprobar ubicación actual**. La detección no sobrescribe el punto guardado: la persona debe revisar la dirección y guardar. Las tiendas que están a más de diez kilómetros también muestran un aviso y la opción de cambiar ubicación.

**Cómo llegar** muestra una ruta por calles en automóvil, distancia, minutos estimados e indicaciones en español. La ruta usa el origen de la cuenta autenticada, no coordenadas enviadas por la URL. El mapa utiliza Leaflet y OpenStreetMap. OSRM se consulta solo al solicitar una ruta y recibe los dos puntos necesarios para calcularla; no se envía el nombre, correo ni domicilio escrito del comprador. Sus resultados no incluyen tráfico en tiempo real. Si no hay ruta o falla el servicio, el enlace a Google Maps continúa disponible.

No necesitas una clave de Google Maps para el enlace externo; consulta la [documentación oficial de Maps URLs](https://developers.google.com/maps/documentation/urls/get-started). La API de rutas sigue la [documentación de OSRM](https://project-osrm.org/docs/v5.24.0/api/).

La configuración inicial usa el servidor público de demostración de OSRM. Puede limitar solicitudes o no estar disponible. Para producción con mayor tráfico, configura una instancia OSRM propia o un proveedor compatible:

```dotenv
OSRM_ROUTING_URL=https://TU-SERVIDOR-OSRM
```

Después de cambiar la URL ejecuta `php artisan config:clear`. Los radios de búsqueda y de aviso se ajustan en `config/discovery.php`. La ubicación del navegador necesita HTTPS o localhost y permiso de la persona.

Las páginas se presentan sin footer. Mi tienda agrupa sus accesos a Resumen, Productos, Mensajes y Apartados recibidos en un desplegable del menú del marketplace.

Validación:

```sh
php artisan test
node --test tests/js/discovery-geo.test.js
npm run build
```
