<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_STORAGE', 'memory'),

    'aliases' => [
        'github' => [
            'storage' => 'memory',
            'prefix' => 'github_',
        ],
    ],

    'storages' => [
        'memory' => [
            'type' => 'roadrunner',
            'driver' => 'memory',
        ],
    ],
];