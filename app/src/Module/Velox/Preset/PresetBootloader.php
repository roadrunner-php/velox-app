<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset;

use App\Module\Velox\Preset\DTO\PresetDefinition;
use App\Module\Velox\Preset\Service\ConfigPresetProvider;
use App\Module\Velox\Preset\Service\PresetMergerService;
use App\Module\Velox\Preset\Service\PresetProviderInterface;
use App\Module\Velox\Preset\Service\PresetValidatorService;
use Spiral\Boot\Bootloader\Bootloader;

final class PresetBootloader extends Bootloader
{
    #[\Override]
    public function defineSingletons(): array
    {
        return [
            PresetProviderInterface::class => fn() => new ConfigPresetProvider($this->initPresets()),
            PresetMergerService::class => PresetMergerService::class,
            PresetValidatorService::class => PresetValidatorService::class,
        ];
    }

    /**
     * @return array<PresetDefinition>
     */
    private function initPresets(): array
    {
        return [
            // Web Server - Basic HTTP server setup
            new PresetDefinition(
                name: 'web-server',
                displayName: 'Web Server',
                description: 'Basic HTTP server with essential middleware for web applications',
                pluginNames: [
                    'server',
                    'logger',
                    'http',
                    'headers',
                    'gzip',
                    'static',
                    'fileserver',
                    'status',
                ],
                tags: ['web', 'http', 'basic'],
                priority: 10,
            ),

            // Queue Server - Job processing setup
            new PresetDefinition(
                name: 'queue-server',
                displayName: 'Queue Server',
                description: 'Job queue processing server with multiple queue drivers',
                pluginNames: [
                    'server',
                    'logger',
                    'jobs',
                    'amqp',
                    'redis',
                    'kv',
                    'status',
                    'metrics',
                ],
                tags: ['jobs', 'queue', 'processing'],
                priority: 10,
            ),

            // Workflow Server - Temporal workflow engine
            new PresetDefinition(
                name: 'workflow-server',
                displayName: 'Workflow Server',
                description: 'Temporal workflow engine for complex business processes',
                pluginNames: [
                    'server',
                    'logger',
                    'temporal',
                    'status',
                    'metrics',
                    'otel',
                ],
                tags: ['workflow', 'temporal', 'orchestration'],
                priority: 10,
            ),

            // K8s - Kubernetes optimized setup
            new PresetDefinition(
                name: 'k8s',
                displayName: 'Kubernetes',
                description: 'Kubernetes-optimized configuration with health checks and observability',
                pluginNames: [
                    'status',
                    'metrics',
                    'prometheus',
                    'otel',
                    'logger',
                ],
                tags: ['kubernetes', 'k8s', 'observability', 'monitoring'],
                priority: 5,
            ),

            // Full Stack - Complete RoadRunner setup
            new PresetDefinition(
                name: 'full-stack',
                displayName: 'Full Stack',
                description: 'Complete RoadRunner setup with all major features',
                pluginNames: [
                    'server',
                    'logger',
                    'appLogger',
                    'http',
                    'headers',
                    'gzip',
                    'static',
                    'fileserver',
                    'proxy',
                    'send',
                    'jobs',
                    'amqp',
                    'redis',
                    'sqs',
                    'kv',
                    'memory',
                    'grpc',
                    'rpc',
                    'status',
                    'metrics',
                    'prometheus',
                    'otel',
                    'tcp',
                ],
                tags: ['complete', 'full-featured', 'production'],
                priority: 15,
            ),

            // API Server - REST API focused setup
            new PresetDefinition(
                name: 'api-server',
                displayName: 'API Server',
                description: 'REST API server with JSON handling and CORS support',
                pluginNames: [
                    'server',
                    'logger',
                    'http',
                    'headers',
                    'gzip',
                    'proxy',
                    'status',
                    'metrics',
                    'prometheus',
                ],
                tags: ['api', 'rest', 'json'],
                priority: 10,
            ),

            // Microservices - Service mesh ready
            new PresetDefinition(
                name: 'microservices',
                displayName: 'Microservices',
                description: 'Microservices architecture with gRPC and observability',
                pluginNames: [
                    'server',
                    'logger',
                    'http',
                    'grpc',
                    'rpc',
                    'status',
                    'metrics',
                    'prometheus',
                    'otel',
                    'sentry-collector',
                ],
                tags: ['microservices', 'grpc', 'distributed'],
                priority: 10,
            ),

            // Monitoring - Observability and monitoring
            new PresetDefinition(
                name: 'monitoring',
                displayName: 'Monitoring',
                description: 'Enhanced monitoring and observability stack',
                pluginNames: [
                    'metrics',
                    'prometheus',
                    'otel',
                    'status',
                    'appLogger',
                ],
                tags: ['monitoring', 'observability', 'metrics'],
                priority: 5,
            ),

            // Security - Security-focused setup
            new PresetDefinition(
                name: 'security',
                displayName: 'Security',
                description: 'Security-focused configuration with proxy and headers',
                pluginNames: [
                    'headers',
                    'proxy',
                    'status',
                    'appLogger',
                ],
                tags: ['security', 'headers', 'proxy'],
                priority: 5,
            ),
        ];
    }
}
