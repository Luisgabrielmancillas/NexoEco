<?php

namespace App\Support;

class DemoCatalog
{
    /** Three categories per marketplace department; no restricted goods or real brands. */
    public const CATEGORIES = [
        ['Comida mexicana', ['Tostadas de temporada', 'Enchiladas caseras', 'Tacos de verduras', 'Sopa del día']],
        ['Panadería', ['Pan artesanal', 'Conchas de vainilla', 'Bollos integrales', 'Pan de elote']],
        ['Repostería', ['Galletas de avena', 'Pastel de chocolate', 'Brownies caseros', 'Muffins de naranja']],
        ['Frutas y verduras', ['Canasta de frutas', 'Verduras de temporada', 'Limón local', 'Mango de temporada']],
        ['Abarrotes', ['Arroz integral', 'Frijoles de la región', 'Granola artesanal', 'Avena natural']],
        ['Bebidas', ['Café molido', 'Té de hierbas', 'Agua de fruta', 'Chocolate de mesa']],
        ['Higiene personal', ['Jabón artesanal', 'Champú sólido', 'Toalla de algodón', 'Cepillo de bambú']],
        ['Cuidado personal', ['Bolsa de aseo', 'Esponja reutilizable', 'Peine de madera', 'Estuche de viaje']],
        ['Salud y bienestar', ['Botella reutilizable', 'Tapete de ejercicio', 'Cojín de descanso', 'Banda de estiramiento']],
        ['Artesanías', ['Pulsera tejida', 'Canasta de palma', 'Figura decorativa', 'Bolsa bordada']],
        ['Regalos', ['Caja de regalo', 'Vela aromática', 'Tarjeta ilustrada', 'Set de papelería']],
        ['Papelería', ['Libreta artesanal', 'Lápices de colores', 'Agenda semanal', 'Separador ilustrado']],
        ['Mascotas y accesorios', ['Collar ajustable', 'Correa de paseo', 'Plato para mascota', 'Pañoleta para mascota']],
        ['Mascotas y juguetes', ['Pelota de juego', 'Juguete de cuerda', 'Rascador pequeño', 'Mordedera de juguete']],
        ['Mascotas y descanso', ['Cama acolchada', 'Manta lavable', 'Cojín para mascota', 'Tapete de descanso']],
        ['Hogar y cerámica', ['Taza de cerámica', 'Plato decorativo', 'Florero artesanal', 'Maceta pintada']],
        ['Hogar y decoración', ['Cojín decorativo', 'Mantel individual', 'Portarretratos', 'Organizador de mesa']],
        ['Jardín', ['Maceta pequeña', 'Regadera manual', 'Palita de jardín', 'Kit de semillas']],
        ['Ropa', ['Playera de algodón', 'Camisa ligera', 'Sudadera básica', 'Blusa de temporada']],
        ['Calzado', ['Sandalias de paseo', 'Tenis de tela', 'Pantuflas cómodas', 'Zapatos casuales']],
        ['Accesorios de moda', ['Bolsa de tela', 'Sombrero de playa', 'Cinturón tejido', 'Pañuelo estampado']],
        ['Electrónica y accesorios', ['Soporte de escritorio', 'Cable de carga', 'Organizador de cables', 'Funda protectora']],
        ['Computación', ['Tapete para ratón', 'Base para laptop', 'Funda de teclado', 'Soporte de monitor']],
        ['Telefonía', ['Funda para celular', 'Soporte de celular', 'Correa para teléfono', 'Estuche de accesorios']],
    ];

    public static function image(int $category): string
    {
        return '/images/demo/categoria-'.str_pad((string) ($category + 1), 2, '0', STR_PAD_LEFT).'.svg';
    }

    public static function email(string $kind, int $index): string
    {
        return sprintf('nexoeco.demo.%s.%04d@example.test', $kind, $index);
    }

    public static function sku(int $store, int $product): string
    {
        return sprintf('NE-DEMO-%04d-%04d', $store, $product);
    }
}
