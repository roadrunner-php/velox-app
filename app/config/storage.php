<?php

declare(strict_types=1);

use App\Application\Storage\RRFilesystemAdapter;
use Spiral\Goridge\RPC\RPC;

return [
    'default' => 'default',
    'servers' => [
        'rr' => [
            'adapter' => RRFilesystemAdapter::class,
            'options' => [
                'rpc' => RPC::create('tcp://127.0.0.1:6002'),
                'bucket' => 'uploads',
            ],
        ],
    ],
    'buckets' => [
        'default' => [
            'server' => 'rr',
            'distribution' => 'uploads',
        ],
    ],
];
