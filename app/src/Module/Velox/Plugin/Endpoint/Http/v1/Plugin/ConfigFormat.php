<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

enum ConfigFormat: string
{
    case TOML = 'toml';
    case JSON = 'json';
    case Dockerfile = 'dockerfile';
    case Docker = 'docker';
}

