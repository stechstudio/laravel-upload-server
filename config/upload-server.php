<?php

return [
    'disk'          => env('UPLOAD_SERVER_DISK', 'local'),
    'path'          => env('UPLOAD_SERVER_PATH', 'uploads'),
    'partials_path' => env('UPLOAD_SERVER_PARTIALS_PATH', 'uploads/partials'),

    'log'       => env('UPLOAD_SERVER_LOG', true),
    'log_level' => env('UPLOAD_SERVER_LOG_LEVEL', 'debug'),

    'cleanup_interval' => env('UPLOAD_SERVER_CLEANUP', '12 hours'),

    'default'  => 'filepond',

    // Server-specific config
    'filepond' => [
        'allow_delete' => env('UPLOAD_FILEPOND_ALLOW_DELETE', true),
    ]
];
