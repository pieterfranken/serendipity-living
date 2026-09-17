<?php

return [
    'disk' => env('VILLA_LAYOUTS_DISK', 'local'),
    // Layout archives must never be served as public static assets.
    'zip_dir' => 'uploads/protected/villa-layout-zips',
    'signed_url_ttl_minutes' => env('VILLA_LAYOUTS_TTL', 30),
    'rate_limit' => [
        'max' => env('VILLA_LAYOUTS_RATE_MAX', 10),
        'decay_minutes' => env('VILLA_LAYOUTS_RATE_DECAY', 1),
    ],
    'max_zip_size_mb' => env('VILLA_LAYOUTS_MAX_MB', 1024),
    'memory_limit' => env('VILLA_LAYOUTS_MEMORY', '1024M'),
    'remove_zip_when_disabled' => env('VILLA_LAYOUTS_REMOVE_ON_DISABLE', false),
];

