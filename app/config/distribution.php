<?php

declare(strict_types=1);

use App\Application\Storage\RRSignedResolver;
use Spiral\Goridge\RPC\RPC;

return [
    'default' => env('DISTRIBUTION_RESOLVER', 'uploads'),

    'resolvers' => [
        'uploads' => [
            'type' => RRSignedResolver::class,
            'options' => [
                'rpc' => RPC::create('tcp://127.0.0.1:6002'),
                'bucket' => 'uploads',
            ],
        ],
    ],
];
