<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) — SEC2-05
|--------------------------------------------------------------------------
|
| API hanya boleh dipanggil JavaScript dari origin aplikasi sendiri (APP_URL), bukan dari situs
| mana pun ('*' bawaan Laravel). Frontend lain yang sah ditambahkan lewat CORS_ALLOWED_ORIGINS
| (dipisah koma, mis. "https://sekolah.sch.id,https://app.sekolah.sch.id").
|
| CORS hanya berlaku untuk browser; Postman, OWASP ZAP, curl, dan aplikasi server tidak terpengaruh.
| Token dikirim lewat header Authorization (bukan cookie), sehingga supports_credentials = false.
|
*/

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],

    'allowed_origins' => array_values(array_filter(array_map(
        fn (string $origin): string => rtrim(trim($origin), '/'),
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', (string) env('APP_URL', ''))),
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
