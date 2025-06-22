<?php

declare(strict_types=1);

return [
    'directories' => [
        directory('app'),
    ],
    'exclude' => [
        directory('app') . '/config',
        directory('runtime'),
    ],
    'load' => [
        'classes' => true,
        'enums' => false,
        'interfaces' => true,
    ],
];
