<?php

return [
    'disk' => env('VILLA_RENDERS_DISK', 'local'),
    'zip_dir' => env('VILLA_RENDERS_ZIP_DIR', 'media/renders/zips'),
    'signed_url_ttl_minutes' => env('VILLA_RENDERS_TTL', 30),
    'rate_limit' => [
        'max' => env('VILLA_RENDERS_RATE_MAX', 10),
        'decay_minutes' => env('VILLA_RENDERS_RATE_DECAY', 1),
    ],
    'max_zip_size_mb' => env('VILLA_RENDERS_MAX_MB', 1024),
    'remove_zip_when_disabled' => env('VILLA_RENDERS_REMOVE_ON_DISABLE', false),
];

