<?php

return [
    'client_id' => env('MERCADOPAGO_CLIENT_ID'),
    'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
    'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
    'sandbox' => env('MERCADOPAGO_SANDBOX', true),
    'currency' => 'MXN',
];
