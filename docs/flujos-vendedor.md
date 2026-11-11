# Registro, catálogo y opiniones

- El registro de comprador y vendedor utiliza un layout de acceso sin navegación del marketplace. Las correcciones de documentos reconocen la sesión y abren el paso de documentos.
- La descripción de tienda admite entre 20 y 500 caracteres. Las descripciones existentes se conservan; al editarlas deberán respetar el límite. En el perfil público se muestran tres líneas y un botón para expandir cuando hace falta.
- `config/store-categories.php` define los giros y sus subcategorías. La migración `2026_11_10_000000_add_seller_review_replies.php` agrega las subcategorías al catálogo sin borrar las anteriores. Las tiendas antiguas deben elegir un giro válido antes de publicar o editar productos. Los productos existentes no se reclasifican automáticamente.
- Cada formulario de alta de producto tiene un identificador que se conserva tras errores. El servidor reconoce reenvíos y serializa la creación por tienda. Las validaciones de apartado ocurren antes de guardar; producto, imagen y notificaciones de publicación se revierten si falla la operación.
- El vendedor recibe avisos por favoritos, seguidores y opiniones, además de los avisos existentes de mensajes y apartados. Guardar repetidamente el mismo favorito o seguimiento no repite el aviso.
- En **Opiniones recibidas**, cada vendedor puede contestar únicamente reseñas de su tienda y productos. La respuesta se publica junto a la reseña, aparece en el historial del comprador y genera una notificación. El historial está paginado.
- La navegación principal comienza con el logo y conserva un enlace para saltar al contenido. La eliminación de cuenta añade un diálogo de confirmación con cancelación y conserva la comprobación de contraseña.

Para instalar en otro entorno: ejecutar las migraciones y `npm run build`.
