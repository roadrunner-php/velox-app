<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_STORAGE', 'roadrunner'),

    'aliases' => [
        'github' => 'memory',
    ],

    'storages' => [
        'memory' => [
            'type' => 'roadrunner',
            'driver' => 'memory',
        ],
    ],
];