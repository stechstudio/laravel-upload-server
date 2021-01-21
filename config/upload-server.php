<?php

return [
    'disk'          => env('UPLOAD_SERVER_DISK', 'local'),
    'path'          => env('UPLOAD_SERVER_PATH', 'uploads'),
    'partials_path' => env('UPLOAD_SERVER_PARTIALS_PATH', 'uploads/partials'),

    'default'  => 'filepond',

    // Server-specific config
    'filepond' => [
        'allow_delete' => env('UPLOAD_FILEPOND_ALLOW_DELETE', true),
    ]
];
