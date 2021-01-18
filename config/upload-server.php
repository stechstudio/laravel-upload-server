<?php

return [
    'temporary_files_path' => env('UPLOAD_TEMP_PATH', 'uploads'),
    'temporary_files_disk' => env('UPLOAD_TEMP_DISK', 'local'),

    'default' => 'filepond',

    // Server-specific config
    'filepond' => [
        'allow_delete' => env('UPLOAD_FILEPOND_ALLOW_DELETE', true),
    ]
];
