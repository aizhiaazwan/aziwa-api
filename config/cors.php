<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    // Development: '*'. Produksi: isi dengan alamat web Anda di .env
    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', '*'))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false, // kita pakai token Bearer, bukan cookie
];