<?php

return [
    // Credentials belong to NexoEco, which receives subscription payments.
    'access_token' => env('MERCADOPAGO_SUBSCRIPTIONS_ACCESS_TOKEN'),
    'webhook_secret' => env('MERCADOPAGO_SUBSCRIPTIONS_WEBHOOK_SECRET'),
    'collector_id' => env('MERCADOPAGO_SUBSCRIPTIONS_COLLECTOR_ID'),
    'plans' => [
        'gratis' => [
            'name' => 'Básico',
            'price' => 0,
            'product_limit' => 0, // 0 porque no tiene productos promocionados
            'priority' => 0,
            'description' => 'Para negocios que apenas comienzan su camino digital.',
            'quick_reply_limit' => 0,
            'automation_limit' => 0,
            'pin_limit' => 0,
            'offer_limit' => 0,
            'benefits' => [
            'Tienda básica en la plataforma',
            'Límite de catálogo sin costo',
            'Soporte estándar'
        ],
        ],
        'impulso' => [
            'name' => 'Impulso', 'price' => 149, 'product_limit' => 5, 'priority' => 1,
            'description' => 'Para negocios que buscan más clientes.',
            'quick_reply_limit' => 5, 'automation_limit' => 1, 'pin_limit' => 3, 'offer_limit' => 3,
            'benefits' => ['5 productos promocionados y prioridad media', 'Tienda en recomendaciones e insignia de patrocinado', 'Estadísticas básicas de visitas, chat y apartados', '5 respuestas rápidas y 1 mensaje automático', 'Personalización básica y 3 productos fijados', 'Hasta 3 promociones especiales activas'],
        ],
        'pro' => [
            'name' => 'Pro', 'price' => 299, 'product_limit' => 20, 'priority' => 2,
            'description' => 'Para negocios que buscan crecer y profesionalizarse.',
            'quick_reply_limit' => null, 'automation_limit' => 2, 'pin_limit' => 10, 'offer_limit' => null,
            'benefits' => ['20 productos promocionados y prioridad alta', 'Recomendaciones preferentes e insignia de patrocinado', 'Estadísticas avanzadas y reportes descargables', 'Respuestas rápidas ilimitadas y automatizaciones personalizables', 'Personalización avanzada y 10 productos fijados', 'Promociones ilimitadas, programación y calendario de apartados'],
        ],
    ],
    'comparison' => [
            ['Productos promocionados', '0', '5', '20'],
            ['Prioridad en búsquedas', 'Ninguna', 'Media', 'Alta'],
            ['Tienda en recomendaciones', 'No', 'Sí', 'Preferente'],
            ['Insignia de patrocinado', 'No', 'Sí', 'Sí'],
            ['Estadísticas de visitas', 'Básicas', 'Básicas', 'Avanzadas'],
            ['Estadísticas de contactos por chat', 'No', 'Sí', 'Sí'],
            ['Respuestas rápidas en chat', '0', '5', 'Ilimitadas'],
            ['Mensajes automáticos', 'No', '1 de bienvenida', 'Bienvenida y fuera de horario'],
            ['Personalización de tienda', 'Básica', 'Básica', 'Avanzada'],
            ['Productos fijados en tienda', '0', '3', '10'],
            ['Promociones especiales', '0', '3 activas', 'Ilimitadas'],
            ['Estadísticas de apartados', 'No', 'Básicas', 'Detalladas'],
            ['Programar promociones y novedades', 'No', '-', 'Sí'],
            ['Reportes descargables', 'No', '-', 'Sí'],
            ['Calendario y recordatorios de apartados', 'No', '-', 'Sí'],
        ],
];
