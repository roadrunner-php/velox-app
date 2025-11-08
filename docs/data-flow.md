# Data Flow Guide

This document traces how data flows through the Velox Application for common use cases, showing the complete journey
from user input to output.

## Table of Contents

1. [Building Binary from Plugin Selection](#1-building-binary-from-plugin-selection)
2. [Building Binary from Presets](#2-building-binary-from-presets)
3. [Listing Plugins via HTTP API](#3-listing-plugins-via-http-api)
4. [Resolving Dependencies](#4-resolving-dependencies)
5. [Generating Configuration Files](#5-generating-configuration-files)
6. [Docker Build Process](#6-docker-build-process)

---

## 1. Building Binary from Plugin Selection

**Use Case**: User wants to build a custom RoadRunner binary with specific plugins.

### Step-by-Step Flow

#### Step 1: User Input

```bash
php app.php velox:build --plugins=http,logger,server,jobs --output=/output
```

**Data Structure**:

```php
$input = [
    'plugins' => ['http', 'logger', 'server', 'jobs'],
    'output' => '/output',
    'githubToken' => null,
]
```

#### Step 2: Console Command Handler

**File**: `BuildBinary` console command

```php
// Inject ConfigurationBuilder
ConfigurationBuilder $builder

// Parse input
$pluginNames = ['http', 'logger', 'server', 'jobs'];
$outputDir = '/output';
```

#### Step 3: Build Configuration

**Service**: `ConfigurationBuilder::buildConfiguration()`

```php
$config = $builder->buildConfiguration($pluginNames, $githubToken);
```

**Internal Flow**:

```
ConfigurationBuilder::buildConfiguration()
    ↓
ConfigurationGeneratorService::buildConfigFromSelection()
    ↓
For each plugin name:
    PluginProviderInterface::getPluginByName()
    ↓
    Retrieve Plugin DTO from PluginsBootloader
    ↓
    Separate into GitHub/GitLab plugins
    ↓
Construct VeloxConfig:
    - roadrunner: RoadRunnerConfig(ref: 'v2025.1.1')
    - github: GitHubConfig(plugins: [http, logger, server, jobs])
    - gitlab: GitLabConfig(plugins: [])
```

**Data Transformation**:

```php
// Input
['http', 'logger', 'server', 'jobs']

// After plugin lookup
[
    Plugin(name: 'http', ref: 'v5.0.2', owner: 'roadrunner-server', ...),
    Plugin(name: 'logger', ref: 'v5.0.2', owner: 'roadrunner-server', ...),
    Plugin(name: 'server', ref: 'v5.0.2', owner: 'roadrunner-server', ...),
    Plugin(name: 'jobs', ref: 'v5.0.2', owner: 'roadrunner-server', ...),
]

// Final VeloxConfig
VeloxConfig {
    roadrunner: RoadRunnerConfig { ref: 'v2025.1.1' },
    github: GitHubConfig {
        token: null,
        plugins: [/* 4 plugins */]
    },
    gitlab: GitHubConfig { plugins: [] }
}
```

#### Step 4: Resolve Dependencies

**Service**: `DependencyResolverService::resolveDependencies()`

```php
$resolution = $dependencyResolver->resolveDependencies($plugins);
```

**Dependency Tree Example**:

```
User Selected: [http, jobs]
    ↓
http requires: [logger, server]
    ↓
jobs requires: [logger, server, rpc]
    ↓
server requires: [logger]
rpc requires: [logger]
    ↓
Final Required Plugins: [logger, server, rpc, http, jobs]
```

**Algorithm** (Depth-First Search):

```
1. Visit 'http'
   - Mark 'http' as visited
   - Check dependency 'logger'
     - Visit 'logger' (no dependencies)
     - Add 'logger' to resolved
   - Check dependency 'server'
     - Visit 'server'
       - Check dependency 'logger' (already resolved)
     - Add 'server' to resolved
   - Add 'http' to resolved

2. Visit 'jobs'
   - Mark 'jobs' as visited
   - Check dependency 'logger' (already resolved)
   - Check dependency 'server' (already resolved)
   - Check dependency 'rpc'
     - Visit 'rpc'
       - Check dependency 'logger' (already resolved)
     - Add 'rpc' to resolved
   - Add 'jobs' to resolved

Result: [logger, server, rpc, http, jobs]
```

**Data Structure**:

```php
DependencyResolution {
    requiredPlugins: [
        Plugin(name: 'logger', ...),
        Plugin(name: 'server', ...),
        Plugin(name: 'rpc', ...),
        Plugin(name: 'http', ...),
        Plugin(name: 'jobs', ...),
    ],
    conflicts: [],
    isValid: true
}
```

#### Step 5: Validate Configuration

**Service**: `ConfigurationValidatorService::validateConfiguration()`

```php
$validationResult = $validator->validateConfiguration($config);

if (!$validationResult->isValid) {
    throw ValidationException($validationResult->errors);
}
```

**Validation Checks**:

- All dependencies present
- No circular dependencies
- Valid GitHub/GitLab tokens if needed
- Valid RoadRunner version
- Plugin versions compatible

**Data Structure**:

```php
ValidationResult {
    isValid: true,
    errors: [],
    warnings: []
}
```

#### Step 6: Generate TOML Configuration

**Service**: `ConfigurationGeneratorService::generateToml()`

```php
$tomlContent = $generator->generateToml($config);
```

**TOML Output**:

```toml
[roadrunner]
ref = "v2025.1.1"

[github.plugins.logger]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "logger"

[github.plugins.server]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "server"

[github.plugins.rpc]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "rpc"

[github.plugins.http]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "http"

[github.plugins.jobs]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "jobs"
```

#### Step 7: Build Binary

**Service**: `BinaryBuilderService::buildBinary()`

```php
$buildResult = $binaryBuilder->buildBinary($config, $outputDir);
```

**Internal Process**:

```
1. Generate build hash: sha256(config + timestamp)
   → "a3f5b8c2..."

2. Create temp directory: /tmp/velox-builds/a3f5b8c2...

3. Write TOML to temp: /tmp/velox-builds/a3f5b8c2.../velox.toml

4. Ensure output directory exists: /output

5. Execute Velox:
   Command: vx build -c /tmp/velox-builds/a3f5b8c2.../velox.toml -o /output
   Timeout: 300 seconds

6. Monitor process:
   - Capture stdout
   - Capture stderr
   - Track exit code

7. Verify binary created: /output/rr exists

8. Collect metrics:
   - Build time: 45.3 seconds
   - Binary size: 48,234,567 bytes

9. Cleanup: Delete /tmp/velox-builds/a3f5b8c2...

10. Return BuildResult
```

**Data Structure**:

```php
BuildResult {
    success: true,
    binaryPath: '/output/rr',
    buildTimeSeconds: 45.3,
    binarySizeBytes: 48234567,
    logs: [
        'Downloading roadrunner-server/logger@v5.0.2...',
        'Downloading roadrunner-server/http@v5.0.2...',
        'Building binary...',
        'Build complete',
    ],
    errors: [],
    configPath: '/tmp/velox-builds/a3f5b8c2.../velox.toml',
    buildHash: 'a3f5b8c2...'
}
```

#### Step 8: Output to User

```
✓ Binary built successfully!
  Path: /output/rr
  Size: 46.01 MB
  Time: 45.3s
```

---

## 2. Building Binary from Presets

**Use Case**: User wants to build RoadRunner with a predefined configuration (preset).

### Step-by-Step Flow

#### Step 1: User Input

```bash
php app.php velox:preset:generate --presets=web-server,monitoring
```

**Data Structure**:

```php
$input = [
    'presets' => ['web-server', 'monitoring'],
]
```

#### Step 2: Retrieve Presets

**Service**: `PresetProviderInterface::getPresetByName()`

```php
// For each preset name
$presets = [
    PresetDefinition {
        name: 'web-server',
        description: 'Basic web server setup',
        pluginNames: ['server', 'http', 'logger', 'headers', 'gzip'],
        tags: ['web', 'http'],
        priority: 10
    },
    PresetDefinition {
        name: 'monitoring',
        description: 'Monitoring and observability',
        pluginNames: ['logger', 'metrics', 'prometheus', 'status'],
        tags: ['monitoring', 'observability'],
        priority: 5
    }
]
```

#### Step 3: Merge Presets

**Service**: `PresetMergerService::mergePresets()`

```php
$mergeResult = $presetMerger->mergePresets(['web-server', 'monitoring']);
```

**Merge Process**:

```
1. Sort by priority (descending): [web-server (10), monitoring (5)]

2. Merge plugin lists (union):
   web-server:   [server, http, logger, headers, gzip]
   monitoring:   [logger, metrics, prometheus, status]
   ↓
   merged:       [server, http, logger, headers, gzip, metrics, prometheus, status]
                  (duplicates removed: 'logger' appears only once)

3. Detect conflicts:
   - Check for multiple job drivers: None
   - Check for multiple KV storages: None
   - Check for overlapping middleware: None
   ↓
   No conflicts

4. Generate warnings:
   - None
```

**Data Structure**:

```php
PresetMergeResult {
    mergedPresets: ['web-server', 'monitoring'],
    finalPlugins: ['server', 'http', 'logger', 'headers', 'gzip', 'metrics', 'prometheus', 'status'],
    conflicts: [],
    warnings: [],
    isValid: true
}
```

#### Step 4: Build Configuration from Merged Plugins

**Service**: `ConfigurationBuilder::buildConfiguration()`

```php
$config = $builder->buildConfiguration($mergeResult->finalPlugins);
```

**Data Transformation**:

```php
// Input (from merge)
['server', 'http', 'logger', 'headers', 'gzip', 'metrics', 'prometheus', 'status']

// Output (VeloxConfig)
VeloxConfig {
    roadrunner: RoadRunnerConfig { ref: 'v2025.1.1' },
    github: GitHubConfig {
        plugins: [
            Plugin(name: 'server', ...),
            Plugin(name: 'http', ...),
            Plugin(name: 'logger', ...),
            Plugin(name: 'headers', ...),
            Plugin(name: 'gzip', ...),
            Plugin(name: 'metrics', ...),
            Plugin(name: 'prometheus', ...),
            Plugin(name: 'status', ...),
        ]
    }
}
```

#### Step 5-8: Continue as in Flow 1

(Same as steps 4-8 in "Building Binary from Plugin Selection")

---

## 3. Listing Plugins via HTTP API

**Use Case**: Frontend application wants to display available plugins.

### Step-by-Step Flow

#### Step 1: HTTP Request

```http
GET /api/v1/plugins?category=http&source=official
```

**Query Parameters**:

```php
[
    'category' => 'http',
    'source' => 'official'
]
```

#### Step 2: Route Matching

**Spiral Router** matches request to:

```php
#[Route(route: 'v1/plugins', name: 'plugin.list', methods: ['GET'], group: 'api')]
ListAction::__invoke()
```

#### Step 3: Interceptor Pipeline

**Flow through interceptors** (defined in AppBootloader):

1. **StringToIntParametersInterceptor**: No effect (no int params)
2. **UuidParametersConverterInterceptor**: No effect (no UUID params)
3. Continue to action...

#### Step 4: Filter Validation

**Service**: `ListPluginsFilter` (auto-validated by Spiral)

```php
ListPluginsFilter {
    category: PluginCategory::Http,  // Enum conversion
    source: PluginSource::Official,   // Enum conversion
    search: null
}
```

#### Step 5: Action Execution

**File**: `ListAction::__invoke()`

```php
public function __invoke(ConfigurationBuilder $builder, ListPluginsFilter $filter): ResourceInterface
{
    // Get all plugins
    $plugins = $builder->getAvailablePlugins();

    // Apply category filter
    if ($filter->category !== null) {
        $plugins = $builder->getPluginsByCategory($filter->category);
    }

    // Apply source filter
    if ($filter->source !== null) {
        $plugins = array_filter(
            $plugins,
            fn($plugin) => $plugin->source === $filter->source
        );
    }

    return new PluginCollectionResource($plugins);
}
```

**Data Flow**:

```php
// All plugins (from PluginProvider)
$allPlugins = [
    Plugin(name: 'logger', category: Logging, source: Official),
    Plugin(name: 'http', category: Http, source: Official),
    Plugin(name: 'jobs', category: Jobs, source: Official),
    Plugin(name: 'http-caching', category: Http, source: Community),
    ... (50+ plugins)
]

// After category filter (Http)
$filteredByCategory = [
    Plugin(name: 'http', category: Http, source: Official),
    Plugin(name: 'headers', category: Http, source: Official),
    Plugin(name: 'gzip', category: Http, source: Official),
    Plugin(name: 'http-caching', category: Http, source: Community),
]

// After source filter (Official)
$filteredBySource = [
    Plugin(name: 'http', category: Http, source: Official),
    Plugin(name: 'headers', category: Http, source: Official),
    Plugin(name: 'gzip', category: Http, source: Official),
]
```

#### Step 6: Resource Conversion

**Class**: `PluginCollectionResource`

```php
PluginCollectionResource {
    plugins: [/* 3 filtered plugins */],
    total: 3,
    filters: {
        category: 'http',
        source: 'official'
    }
}
```

#### Step 7: JSON Interceptor

**Interceptor**: `JsonResourceInterceptor`

Converts `ResourceInterface::toArray()` to JSON response:

```php
$resource->toArray()  // Called on PluginCollectionResource
↓
[
    'data' => [
        [
            'name' => 'http',
            'ref' => 'v5.0.2',
            'owner' => 'roadrunner-server',
            'repository' => 'http',
            'source' => 'official',
            'category' => 'http',
            'description' => 'HTTP server plugin',
            'dependencies' => ['logger', 'server'],
            'docs_url' => 'https://docs.roadrunner.dev/...'
        ],
        [
            'name' => 'headers',
            ...
        ],
        [
            'name' => 'gzip',
            ...
        ]
    ],
    'total' => 3,
    'filters' => [
        'category' => 'http',
        'source' => 'official'
    ]
]
↓
JSON encoding
↓
HTTP Response with Content-Type: application/json
```

#### Step 8: HTTP Response

```http
HTTP/1.1 200 OK
Content-Type: application/json

{
  "data": [
    {
      "name": "http",
      "ref": "v5.0.2",
      "owner": "roadrunner-server",
      "repository": "http",
      "source": "official",
      "category": "http",
      "description": "HTTP server plugin",
      "dependencies": ["logger", "server"],
      "docs_url": "https://docs.roadrunner.dev/..."
    },
    ...
  ],
  "total": 3,
  "filters": {
    "category": "http",
    "source": "official"
  }
}
```

---

## 4. Resolving Dependencies

**Use Case**: User wants to understand what plugins are required for their selection.

### HTTP API Request

#### Request

```http
POST /api/v1/plugins/dependencies
Content-Type: application/json

{
  "plugins": ["http", "jobs"]
}
```

#### Response Flow

**Step 1**: Parse request body

```php
$input = ['plugins' => ['http', 'jobs']]
```

**Step 2**: Retrieve plugin objects

```php
$selectedPlugins = [
    Plugin(name: 'http', dependencies: ['logger', 'server']),
    Plugin(name: 'jobs', dependencies: ['logger', 'server', 'rpc']),
]
```

**Step 3**: Resolve dependencies

```php
$resolution = $dependencyResolver->resolveDependencies($selectedPlugins);
```

**Dependency Graph**:

```
http → [logger, server]
jobs → [logger, server, rpc]
server → [logger]
rpc → [logger]
logger → []

Resolved order: [logger, server, rpc, http, jobs]
```

**Step 4**: Return response

```json
{
  "required_plugins": [
    {
      "name": "logger",
      "reason": "Required by: http, jobs, server, rpc"
    },
    {
      "name": "server",
      "reason": "Required by: http, jobs"
    },
    {
      "name": "rpc",
      "reason": "Required by: jobs"
    },
    {
      "name": "http",
      "reason": "User selected"
    },
    {
      "name": "jobs",
      "reason": "User selected"
    }
  ],
  "conflicts": [],
  "is_valid": true
}
```

---

## 5. Generating Configuration Files

**Use Case**: User wants to generate velox.toml and Dockerfile without building.

### HTTP API Request

```http
POST /api/v1/plugins/generate-config
Content-Type: application/json

{
  "plugins": ["http", "logger", "server"],
  "format": "toml"
}
```

### Response Flow

**Step 1**: Build configuration

```php
$config = $builder->buildConfiguration(['http', 'logger', 'server']);
```

**Step 2**: Generate TOML

```php
$toml = $generator->generateToml($config);
```

**Step 3**: Return response

```http
HTTP/1.1 200 OK
Content-Type: application/json

{
  "format": "toml",
  "content": "[roadrunner]\nref = \"v2025.1.1\"\n\n[github.plugins.logger]...",
  "plugins": ["logger", "server", "http"],
  "plugin_count": 3
}
```

### Alternative: Dockerfile Format

```http
POST /api/v1/plugins/generate-config
{
  "plugins": ["http", "logger", "server"],
  "format": "dockerfile"
}
```

**Response**:

```http
HTTP/1.1 200 OK

{
  "format": "dockerfile",
  "content": "FROM ghcr.io/roadrunner-server/velox:2025.1.1 as velox\n...",
  "plugins": ["logger", "server", "http"],
  "plugin_count": 3
}
```

---

## 6. Docker Build Process

**Use Case**: User wants to build RoadRunner in a Docker container.

### Generated Dockerfile Flow

#### Generated Dockerfile

```dockerfile
FROM ghcr.io/roadrunner-server/velox:2025.1.1 as velox

ARG APP_VERSION="undefined"
ARG BUILD_TIME="undefined"

# Generate velox configuration
RUN echo '[roadrunner]' > velox.toml
RUN echo 'ref = "v2025.1.1"' >> velox.toml
RUN echo '' >> velox.toml
RUN echo '[github.plugins.logger]' >> velox.toml
RUN echo 'ref = "v5.0.2"' >> velox.toml
...

# Build RoadRunner binary
ENV CGO_ENABLED=0
RUN vx build -c velox.toml -o /usr/bin/

# Runtime stage
FROM php:8.3-cli
COPY --from=velox /usr/bin/rr /usr/bin/rr
COPY . /app
WORKDIR /app
ENTRYPOINT ["/usr/bin/rr", "serve"]
```

#### Build Process

**Step 1**: Build stage

```bash
docker build -t my-roadrunner .
```

**Step 2**: Inside build container

```
1. Start from velox:2025.1.1 image (contains vx tool)
2. Create velox.toml via RUN echo commands
3. Execute: vx build -c velox.toml -o /usr/bin/
4. Velox downloads plugins and builds Go binary
5. Binary written to /usr/bin/rr
```

**Step 3**: Runtime stage

```
1. Start from php:8.3-cli
2. Copy /usr/bin/rr from build stage
3. Copy application files
4. Set working directory to /app
5. Set entrypoint to /usr/bin/rr serve
```

**Step 4**: Run container

```bash
docker run -p 8080:8080 my-roadrunner
```

---

## Data Transformation Summary

### Plugin Name → Binary

```
User Input: "http"
    ↓
PluginProvider lookup
    ↓
Plugin DTO {
    name: 'http',
    ref: 'v5.0.2',
    owner: 'roadrunner-server',
    repository: 'http',
    dependencies: ['logger', 'server']
}
    ↓
Dependency Resolution
    ↓
Required Plugins: [logger, server, http]
    ↓
VeloxConfig {
    roadrunner: { ref: 'v2025.1.1' },
    github: { plugins: [logger, server, http] }
}
    ↓
TOML Generation
    ↓
[roadrunner]
ref = "v2025.1.1"
[github.plugins.logger]
ref = "v5.0.2"
...
    ↓
Binary Build (vx)
    ↓
/output/rr (executable binary, ~46MB)
```

### Preset → Configuration

```
User Input: "web-server"
    ↓
PresetProvider lookup
    ↓
PresetDefinition {
    name: 'web-server',
    pluginNames: ['server', 'http', 'logger', 'headers', 'gzip']
}
    ↓
Preset Merge (if multiple)
    ↓
Final Plugin List: ['server', 'http', 'logger', 'headers', 'gzip']
    ↓
(Continue as Plugin → Binary flow)
```

---

## Error Flow Example

### Circular Dependency Detection

```
User selects: [plugin-a, plugin-b]

plugin-a depends on: [plugin-b, logger]
plugin-b depends on: [plugin-a, logger]

Dependency Resolution:
1. Visit plugin-a
2. Mark plugin-a as visited
3. Check dependency plugin-b
4. Visit plugin-b
5. Mark plugin-b as visited
6. Check dependency plugin-a
7. plugin-a is in visited list!
8. Throw CircularDependencyException

Result:
DependencyResolution {
    requiredPlugins: [],
    conflicts: [
        ConflictInfo {
            pluginName: 'plugin-a',
            conflictType: CircularDependency,
            message: 'Circular dependency detected',
            conflictingPlugins: ['plugin-a', 'plugin-b']
        }
    ],
    isValid: false
}
```

This comprehensive data flow guide shows exactly how data transforms as it moves through the system, making it easier to
debug issues and understand the system's behavior.
