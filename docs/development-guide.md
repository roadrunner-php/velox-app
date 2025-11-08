# Development Guide

This guide provides practical instructions for developers working on the Velox Application, including how to add new
features, extend existing functionality, and understand common development patterns.

## Table of Contents

1. [Adding New Plugins](#1-adding-new-plugins)
2. [Creating New Presets](#2-creating-new-presets)
3. [Adding HTTP Endpoints](#3-adding-http-endpoints)
4. [Adding Console Commands](#4-adding-console-commands)
5. [Extending Plugin Providers](#5-extending-plugin-providers)
6. [Custom Dependency Resolution Logic](#6-custom-dependency-resolution-logic)
7. [Adding New Bootloaders](#7-adding-new-bootloaders)
8. [Testing Guidelines](#8-testing-guidelines)
9. [Code Style and Conventions](#9-code-style-and-conventions)

---

## 1. Adding New Plugins

### When to Add a New Plugin

Add a new plugin when:

- A new official RoadRunner plugin is released
- A community plugin becomes stable and widely used
- You want to support a custom or private plugin

### Step-by-Step Process

#### Step 1: Open PluginsBootloader

**File**: `app/src/Module/Velox/Plugin/PluginsBootloader.php`

#### Step 2: Choose the Appropriate Method

- **Official plugins**: Add to `initCorePlugins()` method
- **Community plugins**: Add to `initCommonPlugins()` method

#### Step 3: Create Plugin Instance

```php
new Plugin(
    name: 'my-plugin',                          // Unique identifier
    ref: $env->get('RR_PLUGIN_MY_PLUGIN', 'v1.0.0'),  // Version with env override
    owner: 'roadrunner-server',                 // GitHub/GitLab owner
    repository: 'my-plugin',                    // Repository name
    repositoryType: PluginRepository::Github,   // Github or GitLab
    source: PluginSource::Official,             // Official or Community
    dependencies: ['logger', 'server'],         // Required plugins
    description: 'My plugin description',       // Human-readable description
    category: PluginCategory::Core,             // Categorization
    docsUrl: 'https://docs.roadrunner.dev/my-plugin',  // Documentation link
    folder: null,                               // Optional: sub-folder in repo
    replace: null,                              // Optional: Go module replacement
)
```

#### Step 4: Determine Dependencies

Dependencies are critical for proper plugin ordering. Ask yourself:

- Does this plugin require logging? → Add `'logger'`
- Does it extend HTTP functionality? → Add `'http'`
- Does it need RPC? → Add `'rpc'`
- Is it a job driver? → Add `'jobs'`
- Is it a KV driver? → Add `'kv'`

**Dependency Guidelines**:

- Always include `'logger'` unless the plugin has no logging
- Server-related plugins typically need `'logger'` and `'server'`
- HTTP middleware needs `'logger'` and `'http'`
- Job drivers need `'logger'` and `'jobs'`

#### Step 5: Choose the Right Category

**Available Categories**:

```php
PluginCategory::Core          // Core functionality
PluginCategory::Logging       // Logging
PluginCategory::Http          // HTTP server/middleware
PluginCategory::Jobs          // Job queues
PluginCategory::Kv            // Key-value storage
PluginCategory::Metrics       // Metrics
PluginCategory::Grpc          // gRPC
PluginCategory::Monitoring    // Health/status
PluginCategory::Network       // Network protocols
PluginCategory::Broadcasting  // WebSocket/broadcasting
PluginCategory::Workflow      // Workflow engines
PluginCategory::Observability // Tracing/observability
```

#### Example: Adding a New Cache Plugin

```php
// In initCommonPlugins() method
new Plugin(
    name: 'redis-cache',
    ref: $env->get('RR_PLUGIN_REDIS_CACHE', 'v2.0.0'),
    owner: 'my-org',
    repository: 'rr-redis-cache',
    repositoryType: PluginRepository::Github,
    source: PluginSource::Community,
    dependencies: ['logger', 'kv', 'redis'],
    description: 'Redis-based caching plugin for RoadRunner',
    category: PluginCategory::Kv,
    docsUrl: 'https://github.com/my-org/rr-redis-cache',
),
```

### Special Cases

#### Plugin in Subdirectory

If the plugin is in a subdirectory of the repository:

```php
new Plugin(
    name: 'http-caching',
    ref: 'master',
    owner: 'darkweak',
    repository: 'souin',
    repositoryType: PluginRepository::Github,
    source: PluginSource::Community,
    folder: '/plugins/roadrunner',  // Plugin is in this subfolder
    dependencies: ['http'],
    description: 'Cache middleware',
    category: PluginCategory::Http,
),
```

#### Plugin with Go Module Replacement

If the plugin requires a Go module replacement:

```php
new Plugin(
    name: 'custom-plugin',
    ref: 'v1.0.0',
    owner: 'my-org',
    repository: 'custom-plugin',
    repositoryType: PluginRepository::Github,
    source: PluginSource::Community,
    replace: 'github.com/old-module=>github.com/new-module',
    dependencies: ['logger'],
    description: 'Custom plugin with module replacement',
    category: PluginCategory::Core,
),
```

---

## 2. Creating New Presets

### When to Create a Preset

Create presets for common plugin combinations:

- Standard deployments (web server, API server, etc.)
- Specific use cases (job processing, microservices, etc.)
- Technology stacks (Laravel, Symfony, etc.)

### Step-by-Step Process

#### Step 1: Define Preset

Create a new method in a preset provider or add to existing configuration:

**File**: `app/src/Module/Velox/Preset/Service/ConfigPresetProvider.php`

```php
private function getWebServerPreset(): PresetDefinition
{
    return new PresetDefinition(
        name: 'web-server',
        description: 'Complete web server setup with HTTP, monitoring, and caching',
        pluginNames: [
            // Core
            'logger',
            'server',
            'rpc',

            // HTTP Layer
            'http',
            'headers',
            'gzip',
            'static',

            // Monitoring
            'metrics',
            'status',

            // Observability
            'otel',
        ],
        tags: ['web', 'http', 'production'],
        priority: 10,  // Higher priority = overrides in conflicts
    );
}
```

#### Step 2: Register Preset

Add the preset to the provider's preset list:

```php
public function getAllPresets(): array
{
    return [
        $this->getWebServerPreset(),
        $this->getJobProcessorPreset(),
        $this->getMicroservicePreset(),
        // ... other presets
    ];
}
```

### Preset Priority System

**Priority determines merge behavior**:

- **Higher priority (10+)**: Foundation presets (web-server, job-processor)
- **Medium priority (5-9)**: Feature presets (monitoring, caching)
- **Lower priority (1-4)**: Optional enhancements (debugging, profiling)

When merging presets, higher priority settings take precedence.

### Example: Complete Preset Set

```php
// Foundation preset (high priority)
new PresetDefinition(
    name: 'web-server',
    description: 'Basic web server',
    pluginNames: ['server', 'http', 'logger'],
    tags: ['web', 'foundation'],
    priority: 10,
)

// Feature preset (medium priority)
new PresetDefinition(
    name: 'monitoring',
    description: 'Add monitoring capabilities',
    pluginNames: ['metrics', 'prometheus', 'status'],
    tags: ['monitoring', 'production'],
    priority: 7,
)

// Enhancement preset (low priority)
new PresetDefinition(
    name: 'debug',
    description: 'Debugging tools',
    pluginNames: ['appLogger', 'otel'],
    tags: ['debug', 'development'],
    priority: 3,
)
```

**Merging these presets**:

```php
$builder->mergePresets(['web-server', 'monitoring', 'debug']);

// Result (ordered by priority):
// Final plugins: [server, http, logger, metrics, prometheus, status, appLogger, otel]
```

---

## 3. Adding HTTP Endpoints

### Endpoint Structure

All HTTP endpoints follow this pattern:

```
app/src/Module/{Module}/Endpoint/Http/v1/{Resource}/{Action}.php
```

### Step-by-Step Process

#### Step 1: Create Action Class

**File**: `app/src/Module/Velox/Plugin/Endpoint/Http/v1/Plugin/ShowAction.php`

```php
<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\ConfigurationBuilder;
use Spiral\Router\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/plugins/{name}',
    description: 'Get details for a specific plugin',
    summary: 'Show plugin details',
    tags: ['plugins'],
    parameters: [
        new OA\Parameter(
            name: 'name',
            description: 'Plugin name',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string', example: 'http'),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Plugin details',
            content: new OA\JsonContent(ref: PluginResource::class),
        ),
        new OA\Response(
            response: 404,
            description: 'Plugin not found',
        ),
    ],
)]
final readonly class ShowAction
{
    #[Route(route: 'v1/plugins/{name}', name: 'plugin.show', methods: ['GET'], group: 'api')]
    public function __invoke(ConfigurationBuilder $builder, ShowPluginFilter $filter): ResourceInterface
    {
        $plugin = $builder->getAvailablePlugins()
            ->filter(fn($p) => $p->name === $filter->name)
            ->first();

        if ($plugin === null) {
            throw new PluginNotFoundException($filter->name);
        }

        return new PluginResource($plugin);
    }
}
```

#### Step 2: Create Filter (Request Validation)

**File**: `app/src/Module/Velox/Plugin/Endpoint/Http/v1/Plugin/ShowPluginFilter.php`

```php
<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use Spiral\Filters\Attribute\Input\Route;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class ShowPluginFilter extends Filter implements HasFilterDefinition
{
    #[Route]
    public string $name;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition([
            'name' => ['required', 'string', 'max:100'],
        ]);
    }
}
```

#### Step 3: Create Resource (Response Format)

**File**: `app/src/Module/Velox/Plugin/Endpoint/Http/v1/Plugin/PluginResource.php`

```php
<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Endpoint\Http\v1\Plugin;

use App\Application\HTTP\Response\ResourceInterface;
use App\Module\Velox\Plugin\DTO\Plugin;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PluginResource',
    description: 'RoadRunner plugin details',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'http'),
        new OA\Property(property: 'version', type: 'string', example: 'v5.0.2'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'category', type: 'string', example: 'http'),
        new OA\Property(property: 'source', type: 'string', enum: ['official', 'community']),
        new OA\Property(property: 'dependencies', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'docs_url', type: 'string', format: 'uri'),
    ],
)]
final readonly class PluginResource implements ResourceInterface
{
    public function __construct(
        private Plugin $plugin,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->plugin->name,
            'version' => $this->plugin->ref,
            'description' => $this->plugin->description,
            'category' => $this->plugin->category?->value,
            'source' => $this->plugin->source->value,
            'dependencies' => $this->plugin->dependencies,
            'docs_url' => $this->plugin->docsUrl,
            'repository' => [
                'owner' => $this->plugin->owner,
                'name' => $this->plugin->repository,
                'type' => $this->plugin->repositoryType->value,
            ],
        ];
    }
}
```

### POST Endpoint Example

```php
#[OA\Post(
    path: '/api/v1/plugins/generate',
    summary: 'Generate configuration from plugins',
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(
            required: ['plugins'],
            properties: [
                new OA\Property(
                    property: 'plugins',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['http', 'logger'],
                ),
                new OA\Property(property: 'format', type: 'string', enum: ['toml', 'json']),
            ],
        ),
    ),
)]
#[Route(route: 'v1/plugins/generate', methods: ['POST'], group: 'api')]
public function __invoke(ConfigurationBuilder $builder, GenerateFilter $filter): ResourceInterface
{
    $config = $builder->buildConfiguration($filter->plugins);

    $output = match($filter->format) {
        'toml' => $builder->generateToml($config),
        'json' => json_encode($config),
    };

    return new ConfigurationResource($output, $filter->format);
}
```

---

## 4. Adding Console Commands

### Command Structure

All console commands follow this pattern:

```
app/src/Module/{Module}/Endpoint/Console/{CommandName}.php
```

### Step-by-Step Process

#### Step 1: Create Command Class

**File**: `app/src/Module/Velox/BinaryBuilder/Endpoint/Console/BuildBinary.php`

```php
<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Endpoint\Console;

use App\Module\Velox\ConfigurationBuilder;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Console\Command;

#[AsCommand(
    name: 'velox:build',
    description: 'Build RoadRunner binary from plugin selection',
)]
final class BuildBinary extends Command
{
    #[Argument(description: 'Comma-separated list of plugins')]
    private string $plugins = '';

    #[Option(name: 'output', shortcut: 'o', description: 'Output directory')]
    private string $output = './build';

    #[Option(name: 'github-token', description: 'GitHub access token')]
    private ?string $githubToken = null;

    public function __invoke(ConfigurationBuilder $builder): int
    {
        $pluginNames = array_filter(explode(',', $this->plugins));

        if (empty($pluginNames)) {
            $this->error('No plugins specified');
            return self::FAILURE;
        }

        $this->info('Building RoadRunner binary...');
        $this->writeln('Plugins: ' . implode(', ', $pluginNames));

        try {
            // Build configuration
            $config = $builder->buildConfiguration($pluginNames, $this->githubToken);

            // Build binary
            $result = $builder->buildBinary($config, $this->output);

            // Display results
            $this->info('Build completed successfully!');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Binary Path', $result->binaryPath],
                    ['Build Time', number_format($result->buildTimeSeconds, 2) . 's'],
                    ['Binary Size', $this->formatBytes($result->binarySizeBytes)],
                ],
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Build failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return number_format($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
    }
}
```

### Interactive Command Example

```php
#[AsCommand(name: 'velox:interactive')]
final class InteractiveBuild extends Command
{
    public function __invoke(ConfigurationBuilder $builder): int
    {
        // Ask for plugins
        $availablePlugins = $builder->getAvailablePlugins();

        $this->info('Available plugins:');
        foreach ($availablePlugins as $plugin) {
            $this->writeln("  - {$plugin->name}: {$plugin->description}");
        }

        $pluginsInput = $this->ask('Enter plugins (comma-separated)');
        $plugins = array_filter(array_map('trim', explode(',', $pluginsInput)));

        // Confirm
        if (!$this->confirm('Build with these plugins?', true)) {
            $this->writeln('Cancelled');
            return self::SUCCESS;
        }

        // Build
        $config = $builder->buildConfiguration($plugins);
        $result = $builder->buildBinary($config, './build');

        $this->info('Build completed!');
        return self::SUCCESS;
    }
}
```

---

## 5. Extending Plugin Providers

### When to Create a Custom Provider

Create a custom plugin provider when:

- Plugins are stored in a database
- Plugins are fetched from an external API
- You need dynamic plugin discovery

### Step-by-Step Process

#### Step 1: Implement Interface

**File**: `app/src/Module/Velox/Plugin/Service/DatabasePluginProvider.php`

```php
<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Service;

use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginCategory;

final readonly class DatabasePluginProvider implements PluginProviderInterface
{
    public function __construct(
        private PluginRepository $repository,
    ) {}

    public function getAllPlugins(): array
    {
        return $this->repository->findAll();
    }

    public function getOfficialPlugins(): array
    {
        return $this->repository->findBySource(PluginSource::Official);
    }

    public function getCommunityPlugins(): array
    {
        return $this->repository->findBySource(PluginSource::Community);
    }

    public function getPluginByName(string $name): ?Plugin
    {
        return $this->repository->findByName($name);
    }

    public function getPluginsByCategory(PluginCategory $category): array
    {
        return $this->repository->findByCategory($category);
    }

    public function searchPlugins(string $query): array
    {
        return $this->repository->search($query);
    }
}
```

#### Step 2: Register in Bootloader

**File**: `app/src/Module/Velox/Plugin/PluginsBootloader.php`

```php
public function defineSingletons(): array
{
    return [
        PluginProviderInterface::class => fn(
            EnvironmentInterface $env,
            DatabasePluginProvider $dbProvider,
        ) => new CompositePluginProvider(
            providers: [
                new ConfigPluginProvider([/* ... */]),
                $dbProvider,  // Add your custom provider
            ],
        ),
    ];
}
```

### Caching Provider Example

```php
final readonly class CachedPluginProvider implements PluginProviderInterface
{
    public function __construct(
        private PluginProviderInterface $provider,
        private CacheInterface $cache,
        private int $ttl = 3600,
    ) {}

    public function getAllPlugins(): array
    {
        return $this->cache->remember(
            'plugins.all',
            fn() => $this->provider->getAllPlugins(),
            $this->ttl,
        );
    }

    public function getPluginByName(string $name): ?Plugin
    {
        return $this->cache->remember(
            "plugins.{$name}",
            fn() => $this->provider->getPluginByName($name),
            $this->ttl,
        );
    }

    // ... implement other methods
}
```

---

## 6. Custom Dependency Resolution Logic

### Extending Dependency Resolver

#### Step 1: Create Custom Resolver

```php
final readonly class CustomDependencyResolver extends DependencyResolverService
{
    public function resolveDependencies(array $selectedPlugins): DependencyResolution
    {
        // Call parent implementation
        $resolution = parent::resolveDependencies($selectedPlugins);

        // Add custom logic
        $this->addRecommendedPlugins($resolution);
        $this->optimizePluginOrder($resolution);

        return $resolution;
    }

    private function addRecommendedPlugins(DependencyResolution $resolution): void
    {
        // Auto-add logger if not present
        $hasLogger = collect($resolution->requiredPlugins)
            ->contains(fn($p) => $p->name === 'logger');

        if (!$hasLogger) {
            $logger = $this->pluginProvider->getPluginByName('logger');
            $resolution->requiredPlugins[] = $logger;
        }
    }

    private function optimizePluginOrder(DependencyResolution $resolution): void
    {
        // Sort by category priority
        usort($resolution->requiredPlugins, function($a, $b) {
            $priority = [
                PluginCategory::Logging => 1,
                PluginCategory::Core => 2,
                PluginCategory::Http => 3,
                // ... etc
            ];

            return ($priority[$a->category] ?? 99) <=> ($priority[$b->category] ?? 99);
        });
    }
}
```

---

## 7. Adding New Bootloaders

### When to Create a Bootloader

Create a bootloader when:

- Initializing a new module
- Registering new services
- Setting up module dependencies

### Bootloader Structure

```php
<?php

declare(strict_types=1);

namespace App\Module\MyModule;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\BinderInterface;

final class MyModuleBootloader extends Bootloader
{
    // Define which bootloaders this one depends on
    protected const DEPENDENCIES = [
        \App\Application\Bootloader\AppBootloader::class,
    ];

    // Define which bootloaders should run after this one
    protected const LOAD = [];

    // Register singleton services
    public function defineSingletons(): array
    {
        return [
            MyServiceInterface::class => MyService::class,

            // With factory
            MyConfigurableService::class => static fn(
                EnvironmentInterface $env,
                SomeDependency $dependency,
            ) => new MyConfigurableService(
                setting: $env->get('MY_SETTING', 'default'),
                dependency: $dependency,
            ),
        ];
    }

    // Perform initialization logic
    public function boot(BinderInterface $binder): void
    {
        // Bind interfaces
        $binder->bind(MyInterface::class, MyImplementation::class);

        // Register listeners, etc.
    }
}
```

### Register Bootloader

**File**: `app/config/app.php`

```php
return [
    'bootloaders' => [
        // ... existing bootloaders
        \App\Module\MyModule\MyModuleBootloader::class,
    ],
];
```

---

## 8. Testing Guidelines

### Unit Testing Services

```php
use PHPUnit\Framework\TestCase;

final class DependencyResolverServiceTest extends TestCase
{
    public function testResolvesSimpleDependencies(): void
    {
        // Arrange
        $pluginProvider = $this->createMock(PluginProviderInterface::class);
        $pluginProvider->method('getPluginByName')
            ->willReturnMap([
                ['http', new Plugin(name: 'http', dependencies: ['logger'])],
                ['logger', new Plugin(name: 'logger', dependencies: [])],
            ]);

        $resolver = new DependencyResolverService($pluginProvider);

        // Act
        $result = $resolver->resolveDependencies([
            new Plugin(name: 'http', dependencies: ['logger']),
        ]);

        // Assert
        $this->assertTrue($result->isValid);
        $this->assertCount(2, $result->requiredPlugins);
        $this->assertEquals('logger', $result->requiredPlugins[0]->name);
        $this->assertEquals('http', $result->requiredPlugins[1]->name);
    }

    public function testDetectsCircularDependencies(): void
    {
        // Test circular dependency detection
        // ...
    }
}
```

### Integration Testing HTTP Endpoints

```php
use Spiral\Testing\TestCase;

final class PluginListActionTest extends TestCase
{
    public function testListPlugins(): void
    {
        $response = $this->get('/api/v1/plugins');

        $response->assertOk();
        $response->assertBodyContains('data');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['name', 'version', 'description'],
            ],
        ]);
    }

    public function testFilterByCategory(): void
    {
        $response = $this->get('/api/v1/plugins?category=http');

        $response->assertOk();

        $data = json_decode($response->getBody()->__toString(), true);
        foreach ($data['data'] as $plugin) {
            $this->assertEquals('http', $plugin['category']);
        }
    }
}
```

---

## 9. Code Style and Conventions

### PHP Code Style

The project follows PSR-12 with some additions:

```php
<?php

declare(strict_types=1);  // Always use strict types

namespace App\Module\Velox\Example;

use Vendor\Package\Class;  // One use statement per line, sorted alphabetically

/**
 * Class description
 */
final readonly class ExampleClass  // Prefer final and readonly when possible
{
    public function __construct(
        private ServiceInterface $service,  // Constructor property promotion
        private string $setting,
    ) {}

    public function exampleMethod(string $param): array
    {
        // Use typed properties and return types
        // Use array destructuring
        [$first, $second] = $this->getData();

        // Use null coalescing
        $value = $param ?? 'default';

        // Use match expressions
        $result = match($value) {
            'a' => 1,
            'b' => 2,
            default => 0,
        };

        return compact('first', 'second', 'result');
    }

    private function getData(): array
    {
        // Use array unpacking
        return ['value1', 'value2'];
    }
}
```

### Naming Conventions

**Classes**:

- Services: `{Name}Service` (e.g., `BinaryBuilderService`)
- Interfaces: `{Name}Interface` (e.g., `PluginProviderInterface`)
- DTOs: `{Name}` (e.g., `Plugin`, `BuildResult`)
- Enums: `{Name}` (e.g., `PluginCategory`, `ConflictType`)
- Resources: `{Name}Resource` (e.g., `PluginResource`)
- Filters: `{Name}Filter` (e.g., `ListPluginsFilter`)
- Actions: `{Verb}Action` (e.g., `ListAction`, `ShowAction`)
- Bootloaders: `{Name}Bootloader` (e.g., `PluginsBootloader`)

**Methods**:

- Use verb-noun pattern: `getPlugin()`, `buildConfiguration()`, `validatePreset()`
- Boolean methods: prefix with `is`, `has`, `can`: `isValid()`, `hasConflicts()`, `canMerge()`

**Properties**:

- Use descriptive names: `$pluginProvider`, `$configGenerator`
- Avoid abbreviations unless widely understood

### Documentation

All public methods should have docblocks:

```php
/**
 * Resolves dependencies for selected plugins
 *
 * @param array<Plugin> $selectedPlugins Selected plugins to resolve
 * @return DependencyResolution Resolution result with dependencies and conflicts
 * @throws DependencyConflictException When circular dependency detected
 */
public function resolveDependencies(array $selectedPlugins): DependencyResolution
{
    // ...
}
```

### Common Patterns

#### Service Injection

```php
public function __construct(
    private PluginProviderInterface $pluginProvider,
    private DependencyResolverService $resolver,
) {}
```

#### Method Chaining (Fluent Interface)

```php
$builder
    ->buildConfiguration(['http', 'logger'])
    ->generateToml()
    ->writeToFile('/path/to/velox.toml');
```

#### Error Handling

```php
try {
    $result = $this->buildBinary($config, $outputDir);
} catch (BuildException $e) {
    // Handle specific exception
    throw new RuntimeException('Failed to build', 0, $e);
} catch (\Exception $e) {
    // Handle general exception
    throw new RuntimeException('Unexpected error', 0, $e);
} finally {
    // Cleanup
    $this->cleanup();
}
```

---

## Quick Reference

### File Creation Checklist

When creating a new feature:

- [ ] Create DTOs if needed
- [ ] Create service class
- [ ] Add to bootloader (if new module)
- [ ] Create HTTP action (if HTTP endpoint)
- [ ] Create filter for validation (if HTTP endpoint)
- [ ] Create resource for response (if HTTP endpoint)
- [ ] Add OpenAPI documentation
- [ ] Create console command (if CLI needed)
- [ ] Add unit tests
- [ ] Add integration tests
- [ ] Update this documentation

### Common Commands

```bash
# Run tests
composer test

# Check code style
composer cs:fix

# Static analysis
composer psalm

# Refactor (Rector)
composer refactor

# Download RoadRunner
composer rr:download

# Start development server
./rr serve
```

---

## Getting Help

- **Architecture**: See `docs/architecture-overview.md`
- **Module Details**: See `docs/module-reference.md`
- **Data Flow**: See `docs/data-flow.md`
- **Spiral Framework Docs**: https://spiral.dev/docs
- **RoadRunner Docs**: https://roadrunner.dev/docs

Happy coding! 🚀
