<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\DTO;

enum PluginCategory: string
{
    case Core = 'core';
    case Logging = 'logging';
    case Http = 'http';
    case Jobs = 'jobs';
    case Kv = 'kv';
    case Metrics = 'metrics';
    case Grpc = 'grpc';
    case Monitoring = 'monitoring';
    case Network = 'network';
    case Broadcasting = 'broadcasting';
    case Workflow = 'workflow';
    case Observability = 'observability';
}
