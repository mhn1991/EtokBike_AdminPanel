<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile API Base URL
    |--------------------------------------------------------------------------
    |
    | Override this when Android needs a host different from APP_URL. For the
    | emulator, use http://10.0.2.2:8001 instead of http://127.0.0.1:8001.
    |
    */

    'base_url' => env('MOBILE_API_BASE_URL'),

    'manifest_path' => resource_path('mobile/manifest.json'),

    'screens_path' => resource_path('mobile/screens'),
];
