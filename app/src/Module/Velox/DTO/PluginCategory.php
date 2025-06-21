<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

enum PluginCategory: string
{
    case Core = 'Core';
    case Logging = 'Logging';
    case Http = 'HTTP';
    case Jobs = 'Jobs';
    case Kv = 'KV';
    case Metrics = 'Metrics';
    case Grpc = 'gRPC';
    case Monitoring = 'Monitoring';
    case Network = 'Network';
    case Broadcasting = 'Broadcasting';
    case Workflow = 'Workflow';
    case Observability = 'Observability';
}
