# Module Reference Guide

This document provides a detailed reference for each module in the Velox Application, including file locations, class
purposes, and usage examples.

## Table of Contents

1. [Application Layer](#application-layer)
2. [Velox Module](#velox-module)
    - [Plugin Sub-module](#plugin-sub-module)
    - [Configuration Sub-module](#configuration-sub-module)
    - [BinaryBuilder Sub-module](#binarybuilder-sub-module)
    - [Preset Sub-module](#preset-sub-module)
    - [Dependency Sub-module](#dependency-sub-module)
    - [Version Sub-module](#version-sub-module)
    - [Environment Sub-module](#environment-sub-module)
3. [Github Module](#github-module)

---

## Application Layer

**Location**: `app/src/Application/`

### Bootloaders

#### AppBootloader

**File**: `app/src/Application/Bootloader/AppBootloader.php`

**Purpose**: Initializes the domain core and registers HTTP interceptors.

**Responsibilities**:

- Defines the interceptor pipeline for HTTP requests
- Registers domain handler

**Interceptors (in order)**:

1. `ExceptionHandlerInterceptor` - Catches exceptions
2. `JsonResourceInterceptor` - Converts resources to JSON
3. `StringToIntParametersInterceptor` - Type conversion
4. `UuidParametersConverterInterceptor` - UUID conversion

#### ExceptionHandlerBootloader

**File**: `app/src/Application/Bootloader/ExceptionHandlerBootloader.php`

**Purpose**: Configures exception handling and error rendering.

#### LoggingBootloader

**File**: `app/src/Application/Bootloader/LoggingBootloader.php`

**Purpose**: Configures application logging.

### HTTP Infrastructure

#### Interceptors

**Location**: `app/src/Application/HTTP/Interceptor/`

##### ExceptionHandlerInterceptor

**Purpose**: Catches and formats exceptions into standardized error responses.

**Usage**: Automatically applied to all HTTP requests via AppBootloader.

##### JsonResourceInterceptor

**Purpose**: Converts `ResourceInterface` objects into JSON HTTP responses.

**Usage**: Automatically applied to all HTTP requests. Any action returning a `ResourceInterface` will be serialized to
JSON.

##### StringToIntParametersInterceptor

**Purpose**: Converts string route parameters to integers when type-hinted as `int`.

**Example**:

```php
#[Route(route: 'items/{id}')]
public function show(int $id) // String "123" auto-converted to int 123
```

##### UuidParametersConverterInterceptor

**Purpose**: Converts UUID string parameters to UUID objects when type-hinted.

#### Response Abstractions

**Location**: `app/src/Application/HTTP/Response/`

##### ResourceInterface

**File**: `ResourceInterface.php`

**Purpose**: Base interface for all API response resources.

**Methods**:

- `toArray(): array` - Convert resource to array for JSON serialization

##### JsonResource

**File**: `JsonResource.php`

**Purpose**: Generic JSON response wrapper.

**Usage**:

```php
return new JsonResource(['data' => $value]);
```

##### ErrorResource

**File**: `ErrorResource.php`

**Purpose**: Standardized error response format.

**Usage**:

```php
return new ErrorResource('Error message', 404, ['details' => '...']);
```

##### ValidationResource

**File**: `ValidationResource.php`

**Purpose**: Validation error response format.

**Usage**:

```php
return new ValidationResource($errors);
```

##### ResourceCollection

**File**: `ResourceCollection.php`

**Purpose**: Wrapper for collections of resources.

---

## Velox Module

**Location**: `app/src/Module/Velox/`

### ConfigurationBuilder (Facade)

**File**: `app/src/Module/Velox/ConfigurationBuilder.php`

**Purpose**: Main facade providing unified access to all Velox functionality.

**Constructor Dependencies**:

- `PluginProviderInterface` - For plugin retrieval
- `DependencyResolverService` - For dependency resolution
- `ConfigurationValidatorService` - For validation
- `ConfigurationGeneratorService` - For config generation
- `PresetProviderInterface` - For preset retrieval
- `PresetMergerService` - For preset merging
- `PresetValidatorService` - For preset validation
- `BinaryBuilderService` - For binary building

**Key Methods**:

```php
// Plugin operations
getAvailablePlugins(): array<Plugin>
getPluginsByCategory(PluginCategory $category): array<Plugin>
searchPlugins(string $query): array<Plugin>

// Configuration building
buildConfiguration(array $selectedPluginNames, ?string $githubToken = null): VeloxConfig
buildConfigurationFromPresets(array $presetNames, ?string $githubToken = null): VeloxConfig
validateConfiguration(VeloxConfig $config): ValidationResult

// Preset operations
getAvailablePresets(): array<PresetDefinition>
getPresetsByTags(array $tags): array<PresetDefinition>
searchPresets(string $query): array<PresetDefinition>
validatePresets(array $presetNames): PresetValidationResult
mergePresets(array $presetNames): PresetMergeResult

// Dependency resolution
resolveDependencies(array $selectedPluginNames): DependencyResolution

// Output generation
generateToml(VeloxConfig $config): string
generateDockerfile(VeloxConfig $config, string $baseImage = 'php:8.3-cli'): string

// Binary building
buildBinary(VeloxConfig $config, string $outputDirectory): BuildResult
```

**Usage Example**:

```php
// Inject ConfigurationBuilder
public function __construct(private ConfigurationBuilder $builder) {}

// Build configuration from plugins
$config = $this->builder->buildConfiguration(['http', 'logger', 'server']);

// Generate TOML
$toml = $this->builder->generateToml($config);

// Build binary
$result = $this->builder->buildBinary($config, '/output');
```

---

## Plugin Sub-module

**Location**: `app/src/Module/Velox/Plugin/`

**Purpose**: Manages the catalog of RoadRunner plugins.

### Bootloader

#### PluginsBootloader

**File**: `app/src/Module/Velox/Plugin/PluginsBootloader.php:17`

**Purpose**: Initializes all available plugins (50+ plugins including core and community).

**Plugin Initialization**:

- `initCorePlugins()`: Official RoadRunner plugins (40+ plugins)
- `initCommonPlugins()`: Community plugins (5+ plugins)

**Plugin Configuration**:
Each plugin is configured with:

- `name`: Plugin identifier
- `ref`: Git reference (tag, branch)
- `owner`: GitHub/GitLab owner
- `repository`: Repository name
- `repositoryType`: Github or GitLab
- `source`: Official or Community
- `dependencies`: Array of required plugins
- `description`: Plugin description
- `category`: Plugin category
- `docsUrl`: Documentation URL
- `folder`: Optional sub-folder in repository
- `replace`: Optional Go module replacement

**Environment Variable Override**:
Plugin versions can be overridden via environment variables:

```bash
RR_PLUGIN_HTTP=v5.1.0
RR_PLUGIN_LOGGER=v5.0.3
```

### DTOs

#### Plugin

**File**: `app/src/Module/Velox/Plugin/DTO/Plugin.php`

**Purpose**: Represents a single RoadRunner plugin.

**Properties**:

```php
public string $name                    // Plugin identifier (e.g., 'http')
public string $ref                     // Git reference (e.g., 'v5.0.2')
public string $owner                   // Repository owner
public string $repository              // Repository name
public PluginRepository $repositoryType // Github|GitLab
public PluginSource $source            // Official|Community
public ?string $folder                 // Sub-folder in repo
public ?string $replace                // Go module replacement
public array $dependencies             // Required plugin names
public string $description             // Human-readable description
public ?PluginCategory $category       // Category classification
public ?string $docsUrl                // Documentation URL
```

**JSON Serialization**:
Serializes to Velox TOML format:

```json
{
  "ref": "v5.0.2",
  "owner": "roadrunner-server",
  "repository": "http",
  "folder": null,
  "replace": null
}
```

#### PluginCategory (Enum)

**File**: `app/src/Module/Velox/Plugin/DTO/PluginCategory.php`

**Values**:

- `Core`: Core RoadRunner functionality
- `Logging`: Logging plugins
- `Http`: HTTP server and middleware
- `Jobs`: Job queue systems
- `Kv`: Key-value storage
- `Metrics`: Metrics and monitoring
- `Grpc`: gRPC server
- `Monitoring`: Health checks and status
- `Network`: Network protocols (TCP, etc.)
- `Broadcasting`: WebSocket/broadcasting
- `Workflow`: Workflow engines
- `Observability`: Tracing and observability

#### PluginSource (Enum)

**Values**:

- `Official`: Official RoadRunner plugins
- `Community`: Community-contributed plugins

#### PluginRepository (Enum)

**Values**:

- `Github`: GitHub-hosted plugins
- `GitLab`: GitLab-hosted plugins

#### PluginMetadata

**File**: `app/src/Module/Velox/Plugin/DTO/PluginMetadata.php`

**Purpose**: Additional metadata about plugins (usage stats, popularity, etc.)

### Services

#### PluginProviderInterface

**File**: `app/src/Module/Velox/Plugin/Service/PluginProviderInterface.php`

**Purpose**: Contract for retrieving plugins from various sources.

**Methods**:

```php
getAllPlugins(): array<Plugin>
getOfficialPlugins(): array<Plugin>
getCommunityPlugins(): array<Plugin>
getPluginByName(string $name): ?Plugin
getPluginsByCategory(PluginCategory $category): array<Plugin>
searchPlugins(string $query): array<Plugin>
```

#### ConfigPluginProvider

**File**: `app/src/Module/Velox/Plugin/Service/ConfigPluginProvider.php`

**Purpose**: Provides plugins from in-code configuration.

**Usage**:

```php
$provider = new ConfigPluginProvider([
    new Plugin(name: 'http', ref: 'v5.0.2', ...),
    new Plugin(name: 'logger', ref: 'v5.0.2', ...),
]);
```

#### CompositePluginProvider

**File**: `app/src/Module/Velox/Plugin/Service/CompositePluginProvider.php`

**Purpose**: Combines multiple plugin providers into one.

**Usage**:

```php
$composite = new CompositePluginProvider([
    $configProvider,
    $databaseProvider,
    $apiProvider,
]);
```

### HTTP Endpoints

**Base Path**: `/api/v1/plugins`

#### ListAction

**File**: `app/src/Module/Velox/Plugin/Endpoint/Http/v1/Plugin/ListAction.php:86`

**Route**: `GET /api/v1/plugins`

**Purpose**: List all available plugins with optional filtering.

**Query Parameters**:

- `category`: Filter by PluginCategory (optional)
- `source`: Filter by PluginSource (optional)
- `search`: Search by name/description (optional)

**Response**: `PluginCollectionResource`

**Example**:

```bash
GET /api/v1/plugins?category=http&source=official
```

#### ShowAction

**File**: `app/src/Module/Velox/Plugin/Endpoint/Http/v1/Plugin/ShowAction.php`

**Route**: `GET /api/v1/plugins/{name}`

**Purpose**: Get details for a specific plugin.

**Response**: `PluginResource`

#### DependenciesAction

**File**: `app/src/Module/Velox/Plugin/Endpoint/Http/v1/Plugin/Dependency/DependenciesAction.php`

**Route**: `POST /api/v1/plugins/dependencies`

**Purpose**: Resolve dependencies for selected plugins.

**Request Body**:

```json
{
  "plugins": [
    "http",
    "jobs"
  ]
}
```

**Response**: `PluginDependenciesResource`

#### CategoriesAction

**File**: `app/src/Module/Velox/Plugin/Endpoint/Http/v1/Plugin/Category/CategoriesAction.php`

**Route**: `GET /api/v1/plugins/categories`

**Purpose**: List all plugin categories.

**Response**: `PluginCategoriesCollectionResource`

#### GenerateConfigAction

**File**: `app/src/Module/Velox/Plugin/Endpoint/Http/v1/Plugin/GenerateConfigAction.php`

**Route**: `POST /api/v1/plugins/generate-config`

**Purpose**: Generate Velox configuration from plugin selection.

**Request Body**:

```json
{
  "plugins": [
    "http",
    "logger",
    "server"
  ],
  "format": "toml",
  // or "json"
  "githubToken": "optional-token"
}
```

### Exceptions

#### PluginNotFoundException

**File**: `app/src/Module/Velox/Plugin/Exception/PluginNotFoundException.php`

**Purpose**: Thrown when a requested plugin is not found.

---

## Configuration Sub-module

**Location**: `app/src/Module/Velox/Configuration/`

**Purpose**: Handles Velox configuration generation, validation, and serialization.

### DTOs

#### VeloxConfig

**File**: `app/src/Module/Velox/Configuration/DTO/VeloxConfig.php`

**Purpose**: Main Velox configuration object that serializes to TOML format.

**Properties**:

```php
public RoadRunnerConfig $roadrunner    // RoadRunner version config
public GitHubConfig $github            // GitHub plugins config
public GitLabConfig $gitlab            // GitLab plugins config
public ?LogConfig $log                 // Build logging config
public ?DebugConfig $debug             // Debug configuration
```

**Methods**:

- `getAllPlugins(): array<Plugin>` - Get all plugins from all sources

**TOML Serialization**:

```toml
[roadrunner]
ref = "v2025.1.1"

[github]
token = "ghp_..."
[github.plugins.http]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "http"
```

#### RoadRunnerConfig

**File**: `app/src/Module/Velox/Configuration/DTO/RoadRunnerConfig.php`

**Properties**:

- `string $ref` - RoadRunner version (e.g., "v2025.1.1")

#### GitHubConfig

**File**: `app/src/Module/Velox/Configuration/DTO/GitHubConfig.php`

**Properties**:

- `?GitHubToken $token` - GitHub access token (optional)
- `array<Plugin> $plugins` - GitHub-hosted plugins

#### GitLabConfig

**File**: `app/src/Module/Velox/Configuration/DTO/GitLabConfig.php`

**Properties**:

- `?GitLabToken $token` - GitLab access token (optional)
- `?GitLabEndpoint $endpoint` - Custom GitLab endpoint (optional)
- `array<Plugin> $plugins` - GitLab-hosted plugins

#### LogConfig

**File**: `app/src/Module/Velox/Configuration/DTO/LogConfig.php`

**Properties**:

- `LogMode $mode` - Logging mode (production, development)
- `LogLevel $level` - Log level (debug, info, warn, error)

#### ValidationResult

**File**: `app/src/Module/Velox/Configuration/DTO/ValidationResult.php`

**Properties**:

```php
public bool $isValid               // Overall validation status
public array<string> $errors       // Validation errors
public array<string> $warnings     // Validation warnings
```

### Services

#### ConfigurationGeneratorService

**File**: `app/src/Module/Velox/Configuration/Service/ConfigurationGeneratorService.php`

**Purpose**: Generates Velox configurations, TOML files, and Dockerfiles.

**Constructor Parameters**:

```php
public function __construct(
    private PluginProviderInterface $pluginProvider,
    private string $roadRunnerVersion = 'v2025.1.1',
    private ?string $githubToken = null,
    private ?string $gitlabToken = null,
    private ?string $gitlabEndpoint = null,
)
```

**Key Methods**:

##### buildConfigFromSelection()

**File**: `app/src/Module/Velox/Configuration/Service/ConfigurationGeneratorService.php:82`

**Signature**:

```php
public function buildConfigFromSelection(
    array $selectedPluginNames,
    ?string $githubToken = null
): VeloxConfig
```

**Purpose**: Build VeloxConfig from array of plugin names.

**Logic**:

1. If empty array provided, use all official plugins
2. Retrieve each plugin from provider
3. Separate into GitHub and GitLab plugins
4. Construct VeloxConfig with appropriate tokens

##### generateToml()

**File**: `app/src/Module/Velox/Configuration/Service/ConfigurationGeneratorService.php:28`

**Signature**:

```php
public function generateToml(VeloxConfig $config): string
```

**Purpose**: Convert VeloxConfig to TOML format.

**Implementation**: Uses `Devium\Toml\Toml::encode()`

##### generateDockerfile()

**File**: `app/src/Module/Velox/Configuration/Service/ConfigurationGeneratorService.php:33`

**Signature**:

```php
public function generateDockerfile(
    VeloxConfig $config,
    string $veloxVersion = '2025.1.1',
    string $baseImage = 'php:8.3-cli',
): string
```

**Purpose**: Generate multi-stage Dockerfile for building RoadRunner binary.

**Dockerfile Structure**:

1. Build stage: Uses `ghcr.io/roadrunner-server/velox:{version}`
2. Generates velox.toml via line-by-line RUN echo commands
3. Executes `vx build -c velox.toml -o /usr/bin/`
4. Runtime stage: Uses specified base image
5. Copies binary from build stage
6. Sets up entrypoint

**Example Output**:

```dockerfile
FROM ghcr.io/roadrunner-server/velox:2025.1.1 as velox
ARG APP_VERSION="undefined"
ARG BUILD_TIME="undefined"

# Generate velox configuration
RUN echo '[roadrunner]' > velox.toml
RUN echo 'ref = "v2025.1.1"' >> velox.toml
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

#### ConfigurationValidatorService

**File**: `app/src/Module/Velox/Configuration/Service/ConfigurationValidatorService.php`

**Purpose**: Validates VeloxConfig for correctness and completeness.

**Key Methods**:

```php
public function validateConfiguration(VeloxConfig $config): ValidationResult
```

**Validation Checks**:

- Plugin dependency satisfaction
- Version compatibility
- Token presence for private repositories
- Configuration completeness

### Exceptions

#### ValidationException

**File**: `app/src/Module/Velox/Configuration/Exception/ValidationException.php`

**Purpose**: Thrown when configuration validation fails.

**Properties**:

- `array<string> $errors` - Validation error messages

---

## BinaryBuilder Sub-module

**Location**: `app/src/Module/Velox/BinaryBuilder/`

**Purpose**: Orchestrates RoadRunner binary compilation using Velox.

### Bootloader

#### BinaryBuilderBootloader

**File**: `app/src/Module/Velox/BinaryBuilder/BinaryBuilderBootloader.php`

**Purpose**: Initializes binary building services.

**Singletons Defined**:

- `VeloxBinaryRunner`: Configured with vx binary path and timeout
- `BinaryBuilderService`: Configured with dependencies and temp directory

### DTOs

#### BuildResult

**File**: `app/src/Module/Velox/BinaryBuilder/DTO/BuildResult.php`

**Purpose**: Contains the result of a binary build operation.

**Properties**:

```php
public bool $success                  // Build success status
public string $binaryPath             // Path to compiled binary
public float $buildTimeSeconds        // Build duration
public int $binarySizeBytes           // Binary file size
public array<string> $logs            // Build output logs
public array<string> $errors          // Build errors
public string $configPath             // Path to velox.toml used
public string $buildHash              // Unique build identifier
```

### Services

#### BinaryBuilderService

**File**: `app/src/Module/Velox/BinaryBuilder/Service/BinaryBuilderService.php`

**Purpose**: Main service for building RoadRunner binaries.

**Constructor**:

```php
public function __construct(
    private ConfigurationGeneratorService $configGenerator,
    private ConfigurationValidatorService $configValidator,
    private VeloxBinaryRunner $binaryRunner,
    private FilesInterface $files,
    private string $tempDir = '/tmp/velox-builds',
)
```

**Key Methods**:

##### buildBinary()

**File**: `app/src/Module/Velox/BinaryBuilder/Service/BinaryBuilderService.php:28`

**Signature**:

```php
public function buildBinary(VeloxConfig $config, string $outputDirectory): BuildResult
```

**Purpose**: Build RoadRunner binary from VeloxConfig.

**Process**:

1. Generate unique build hash
2. Validate configuration
3. Check vx binary availability
4. Prepare temporary build directory
5. Generate velox.toml file
6. Ensure output directory exists
7. Execute `vx build` command
8. Verify binary creation
9. Collect metrics (time, size)
10. Cleanup temporary files
11. Return BuildResult

**Error Handling**:

- Throws `BuildException` on validation failure
- Throws `BuildException` if vx not available
- Throws `BuildException` on build failure
- Throws `BuildException` if binary not created
- Always cleans up temp directory (finally block)

##### buildFromPluginSelection()

**File**: `app/src/Module/Velox/BinaryBuilder/Service/BinaryBuilderService.php:112`

**Signature**:

```php
public function buildFromPluginSelection(
    array $selectedPlugins,
    string $outputDirectory
): BuildResult
```

**Purpose**: Convenience method to build binary directly from plugin names.

##### buildWithDockerfile()

**File**: `app/src/Module/Velox/BinaryBuilder/Service/BinaryBuilderService.php:122`

**Signature**:

```php
public function buildWithDockerfile(
    VeloxConfig $config,
    string $outputDirectory,
    string $baseImage = 'php:8.3-cli',
): array
```

**Purpose**: Build binary and generate Dockerfile + velox.toml.

**Returns**:

```php
[
    'buildResult' => BuildResult,
    'dockerfilePath' => string,
    'tomlPath' => string,
]
```

##### estimateBuildTime()

**File**: `app/src/Module/Velox/BinaryBuilder/Service/BinaryBuilderService.php:148`

**Signature**:

```php
public function estimateBuildTime(VeloxConfig $config): int
```

**Purpose**: Estimate build time based on plugin count.

**Formula**: `30 + (pluginCount * 5)` seconds

##### checkBuildRequirements()

**File**: `app/src/Module/Velox/BinaryBuilder/Service/BinaryBuilderService.php:162`

**Signature**:

```php
public function checkBuildRequirements(): array
```

**Purpose**: Check if build environment is ready.

**Returns**:

```php
[
    'vx_available' => bool,
    'vx_version' => string,
    'temp_dir_writable' => bool,
    'go_available' => bool,
]
```

#### VeloxBinaryRunner

**File**: `app/src/Module/Velox/BinaryBuilder/Service/VeloxBinaryRunner.php`

**Purpose**: Wrapper around the `vx` CLI tool.

**Constructor**:

```php
public function __construct(
    private string $veloxBinaryPath,
    private int $timeoutSeconds = 300,
)
```

**Key Methods**:

```php
public function build(string $configPath, string $outputDir): array
public function isVeloxAvailable(): bool
public function getVeloxVersion(): string
```

**build() Process**:

1. Construct command: `{vx} build -c {configPath} -o {outputDir}`
2. Execute using Symfony Process
3. Set timeout (default 300 seconds)
4. Capture stdout and stderr
5. Return result array:

```php
[
    'success' => bool,
    'exitCode' => int,
    'output' => string,
    'errorOutput' => string,
]
```

### Console Endpoints

#### BuildBinary

**File**: `app/src/Module/Velox/BinaryBuilder/Endpoint/Console/BuildBinary.php`

**Purpose**: CLI command for building RoadRunner binaries.

**Usage**:

```bash
php app.php velox:build --plugins=http,logger,server --output=/path/to/output
```

### Exceptions

#### BuildException

**File**: `app/src/Module/Velox/BinaryBuilder/Exception/BuildException.php`

**Purpose**: Thrown when binary build fails.

**Properties**:

- `array<string> $errors` - Build error messages

---

## Preset Sub-module

**Location**: `app/src/Module/Velox/Preset/`

**Purpose**: Manages predefined plugin combinations (presets).

### Bootloader

#### PresetBootloader

**File**: `app/src/Module/Velox/Preset/PresetBootloader.php`

**Purpose**: Initializes preset services.

### DTOs

#### PresetDefinition

**File**: `app/src/Module/Velox/Preset/DTO/PresetDefinition.php`

**Purpose**: Defines a named collection of plugins.

**Properties**:

```php
public string $name                   // Preset identifier
public string $description            // Human-readable description
public array<string> $pluginNames     // List of plugin names
public array<string> $tags            // Categorization tags
public int $priority                  // Merge priority (higher wins)
```

**Example**:

```php
new PresetDefinition(
    name: 'web-server',
    description: 'Basic web server setup',
    pluginNames: ['server', 'http', 'logger', 'headers', 'gzip'],
    tags: ['web', 'http'],
    priority: 10,
)
```

#### PresetMergeResult

**File**: `app/src/Module/Velox/Preset/DTO/PresetMergeResult.php`

**Purpose**: Result of merging multiple presets.

**Properties**:

```php
public array<string> $mergedPresets   // Names of merged presets
public array<string> $finalPlugins    // Final plugin list
public array<string> $conflicts       // Conflict errors
public array<string> $warnings        // Merge warnings
public bool $isValid                  // Merge success status
```

#### PresetValidationResult

**File**: `app/src/Module/Velox/Preset/DTO/PresetValidationResult.php`

**Purpose**: Result of preset validation.

**Properties**:

```php
public bool $isValid
public array<string> $errors
public array<string> $warnings
```

### Services

#### PresetProviderInterface

**File**: `app/src/Module/Velox/Preset/Service/PresetProviderInterface.php`

**Purpose**: Contract for retrieving presets.

**Methods**:

```php
getAllPresets(): array<PresetDefinition>
getPresetByName(string $name): ?PresetDefinition
getPresetsByTags(array $tags): array<PresetDefinition>
searchPresets(string $query): array<PresetDefinition>
```

#### ConfigPresetProvider

**File**: `app/src/Module/Velox/Preset/Service/ConfigPresetProvider.php`

**Purpose**: Provides presets from in-code configuration.

#### PresetMergerService

**File**: `app/src/Module/Velox/Preset/Service/PresetMergerService.php:22`

**Purpose**: Merges multiple presets into a single plugin list.

**Key Methods**:

##### mergePresets()

**Signature**:

```php
public function mergePresets(array $presetNames): PresetMergeResult
```

**Process**:

1. Retrieve all presets by name
2. Throw exception if any preset not found
3. Sort by priority (descending)
4. Merge plugin lists (union, no duplicates)
5. Detect conflicts
6. Return merge result

**Priority Handling**: Higher priority presets are processed first, their settings take precedence.

##### detectPresetConflicts()

**File**: `app/src/Module/Velox/Preset/Service/PresetMergerService.php:127`

**Purpose**: Detect conflicts between presets.

**Conflict Groups**:

- `job-drivers`: Multiple job queue drivers
- `kv-storage`: Multiple KV storage backends
- `http-middleware`: Multiple similar HTTP middlewares

**Returns**: Array of warning messages

##### getRecommendedPresets()

**File**: `app/src/Module/Velox/Preset/Service/PresetMergerService.php:103`

**Signature**:

```php
public function getRecommendedPresets(array $selectedPlugins): array<string>
```

**Purpose**: Recommend presets based on already-selected plugins.

**Logic**: Recommends presets where >70% of plugins are already selected.

#### PresetValidatorService

**File**: `app/src/Module/Velox/Preset/Service/PresetValidatorService.php`

**Purpose**: Validates preset combinations.

**Key Methods**:

```php
public function validatePresets(array $presetNames): PresetValidationResult
```

### HTTP Endpoints

**Base Path**: `/api/v1/presets`

#### ListAction

**File**: `app/src/Module/Velox/Preset/Endpoint/Http/v1/Preset/ListAction.php`

**Route**: `GET /api/v1/presets`

**Purpose**: List all available presets.

**Query Parameters**:

- `tags`: Filter by tags (optional)
- `search`: Search by name/description (optional)

**Response**: `PresetCollectionResource`

#### GenerateConfigAction

**File**: `app/src/Module/Velox/Preset/Endpoint/Http/v1/Preset/GenerateConfigAction.php`

**Route**: `POST /api/v1/presets/generate-config`

**Purpose**: Generate configuration from preset selection.

**Request Body**:

```json
{
  "presets": [
    "web-server",
    "job-queue"
  ],
  "format": "toml",
  "githubToken": "optional-token"
}
```

**Response**: Generated configuration in requested format

### Console Endpoints

#### GenerateFromPresets

**File**: `app/src/Module/Velox/Preset/Endpoint/Console/GenerateFromPresets.php`

**Purpose**: CLI command for generating configs from presets.

**Usage**:

```bash
php app.php velox:preset:generate --presets=web-server,job-queue
```

### Exceptions

#### PresetException

**File**: `app/src/Module/Velox/Preset/Exception/PresetException.php`

**Purpose**: Thrown when preset operations fail.

---

## Dependency Sub-module

**Location**: `app/src/Module/Velox/Dependency/`

**Purpose**: Resolves plugin dependencies and detects conflicts.

### DTOs

#### DependencyResolution

**File**: `app/src/Module/Velox/Dependency/DTO/DependencyResolution.php`

**Purpose**: Complete dependency resolution result.

**Properties**:

```php
public array<Plugin> $requiredPlugins  // All required plugins (including transitive)
public array<ConflictInfo> $conflicts  // Detected conflicts
public bool $isValid                   // Resolution success status
```

#### ConflictInfo

**File**: `app/src/Module/Velox/Dependency/DTO/ConflictInfo.php`

**Purpose**: Detailed information about a dependency conflict.

**Properties**:

```php
public string $pluginName                     // Plugin with conflict
public ConflictType $conflictType             // Type of conflict
public string $message                        // Conflict description
public array<string> $conflictingPlugins      // Conflicting plugin names
public ConflictSeverity $severity             // Error or Warning
```

#### ConflictType (Enum)

**File**: `app/src/Module/Velox/Dependency/DTO/ConflictType.php`

**Values**:

- `CircularDependency`: Circular dependency detected
- `MissingDependency`: Required dependency not found
- `VersionConflict`: Same plugin, different versions
- `ResourceConflict`: Resource usage conflicts (e.g., multiple job drivers)

#### ConflictSeverity (Enum)

**File**: `app/src/Module/Velox/Dependency/DTO/ConflictSeverity.php`

**Values**:

- `Error`: Blocking error, build will fail
- `Warning`: Non-blocking warning, build may succeed

#### VersionSuggestion

**File**: `app/src/Module/Velox/Dependency/DTO/VersionSuggestion.php`

**Purpose**: Recommendation for version upgrade.

**Properties**:

```php
public string $pluginName           // Plugin to upgrade
public string $suggestedVersion     // Recommended version
public string $currentVersion       // Current version
public string $reason               // Reason for suggestion
```

### Services

#### DependencyResolverService

**File**: `app/src/Module/Velox/Dependency/Service/DependencyResolverService.php`

**Purpose**: Core dependency resolution logic.

**Constructor**:

```php
public function __construct(
    private PluginProviderInterface $pluginProvider,
)
```

**Key Methods**:

##### resolveDependencies()

**File**: `app/src/Module/Velox/Dependency/Service/DependencyResolverService.php:27`

**Signature**:

```php
public function resolveDependencies(array $selectedPlugins): DependencyResolution
```

**Algorithm**:

1. For each selected plugin:
    - Call `resolveDependenciesRecursive()`
    - Catch `DependencyConflictException` → add to conflicts
    - Catch `PluginNotFoundException` → add to conflicts
2. Remove duplicate plugins
3. Return resolution result

##### resolveDependenciesRecursive()

**File**: `app/src/Module/Velox/Dependency/Service/DependencyResolverService.php:159`

**Signature**:

```php
private function resolveDependenciesRecursive(
    Plugin $plugin,
    array &$resolved,
    array &$visited
): void
```

**Algorithm** (Depth-first search):

1. Check if plugin already visited
    - If yes → throw CircularDependency exception
2. Add plugin to visited list
3. For each dependency:
    - Retrieve dependency plugin
    - If not found → throw PluginNotFoundException
    - Recursively resolve dependency
4. Add plugin to resolved list
5. Remove plugin from visited list

##### detectConflicts()

**File**: `app/src/Module/Velox/Dependency/Service/DependencyResolverService.php:67`

**Signature**:

```php
public function detectConflicts(array $selectedPlugins): array<ConflictInfo>
```

**Checks**:

1. **Version Conflicts**: Same plugin with different versions
2. **Resource Conflicts**: Too many job drivers (>3) → warning

##### suggestCompatibleVersions()

**File**: `app/src/Module/Velox/Dependency/Service/DependencyResolverService.php:110`

**Signature**:

```php
public function suggestCompatibleVersions(array $plugins): array<VersionSuggestion>
```

**Suggestions**:

- Upgrade v4.x plugins to v5.x
- Replace `master` branch with stable version

##### validatePluginCombination()

**File**: `app/src/Module/Velox/Dependency/Service/DependencyResolverService.php:142`

**Signature**:

```php
public function validatePluginCombination(array $plugins): bool
```

**Purpose**: Check if plugin combination is valid (no error-level conflicts).

### Exceptions

#### DependencyConflictException

**File**: `app/src/Module/Velox/Dependency/Exception/DependencyConflictException.php`

**Purpose**: Thrown when circular dependency detected.

**Properties**:

- `array<string> $conflictingPlugins` - Plugins in circular dependency chain

---

## Version Sub-module

**Location**: `app/src/Module/Velox/Version/`

**Purpose**: Manages plugin version checking and updates.

### DTOs

#### VersionUpdateInfo

**File**: `app/src/Module/Velox/Version/DTO/VersionUpdateInfo.php`

**Purpose**: Information about available version updates.

**Properties**:

```php
public string $pluginName
public string $currentVersion
public string $latestVersion
public bool $updateAvailable
public string $changelogUrl
```

### Services

#### GitHubVersionChecker

**File**: `app/src/Module/Velox/Version/Service/GitHubVersionChecker.php`

**Purpose**: Fetches latest plugin versions from GitHub API.

**Key Methods**:

```php
public function getLatestVersion(string $owner, string $repo): string
public function checkForUpdates(Plugin $plugin): VersionUpdateInfo
```

#### PluginVersionManager

**File**: `app/src/Module/Velox/Version/Service/PluginVersionManager.php`

**Purpose**: Manages plugin version updates.

**Key Methods**:

```php
public function checkAllPluginsForUpdates(): array<VersionUpdateInfo>
public function updatePlugin(string $pluginName, string $version): void
```

#### VersionComparisonService

**File**: `app/src/Module/Velox/Version/Service/VersionComparisonService.php`

**Purpose**: Semantic version comparison.

**Key Methods**:

```php
public function compare(string $version1, string $version2): int
public function isNewer(string $version1, string $version2): bool
```

### Console Endpoints

#### UpdatePluginVersions

**File**: `app/src/Module/Velox/Version/Endpoint/Console/UpdatePluginVersions.php`

**Purpose**: CLI command to check and update plugin versions.

**Usage**:

```bash
php app.php velox:version:update
```

### Exceptions

#### VersionCheckException

**File**: `app/src/Module/Velox/Version/Exception/VersionCheckException.php`

**Purpose**: Thrown when version checking fails.

---

## Environment Sub-module

**Location**: `app/src/Module/Velox/Environment/`

**Purpose**: Manages environment file operations.

### Services

#### EnvironmentFileService

**File**: `app/src/Module/Velox/Environment/Service/EnvironmentFileService.php`

**Purpose**: Read and write .env files.

**Key Methods**:

```php
public function read(string $path): array
public function write(string $path, array $data): void
public function update(string $path, string $key, string $value): void
```

### Exceptions

#### EnvironmentFileException

**File**: `app/src/Module/Velox/Environment/Exception/EnvironmentFileException.php`

**Purpose**: Thrown when environment file operations fail.

---

## Github Module

**Location**: `app/src/Module/Github/`

**Purpose**: GitHub integration for fetching repository contributors.

### Bootloaders

#### GithubBootloader

**File**: `app/src/Module/Github/GithubBootloader.php`

**Purpose**: Initializes GitHub client.

#### ContributorsBootloader

**File**: `app/src/Module/Github/Contributors/ContributorsBootloader.php`

**Purpose**: Initializes contributors services.

### Services

#### Client

**File**: `app/src/Module/Github/Client.php`

**Purpose**: GitHub API client wrapper.

**Key Methods**:

```php
public function get(string $path): array
public function getContributors(string $owner, string $repo): array
```

#### ContributorsRepositoryInterface

**File**: `app/src/Module/Github/Contributors/ContributorsRepositoryInterface.php`

**Purpose**: Repository pattern contract for contributors.

**Methods**:

```php
public function getAll(): array<Contributor>
```

#### GithubContributorRepository

**File**: `app/src/Module/Github/Contributors/GithubContributorRepository.php`

**Purpose**: Fetches contributors from GitHub API.

#### CachedContributors

**File**: `app/src/Module/Github/Contributors/CachedContributors.php`

**Purpose**: Caching decorator for contributor repository.

### DTOs

#### Contributor

**File**: `app/src/Module/Github/Contributors/Dto/Contributor.php`

**Properties**:

```php
public string $username
public string $avatarUrl
public int $contributions
public string $profileUrl
```

### HTTP Endpoints

#### ListAction

**File**: `app/src/Module/Github/Contributors/Endpoint/Http/v1/ListAction.php`

**Route**: `GET /api/v1/contributors`

**Purpose**: List project contributors from GitHub.

**Response**: `ContributorResource` collection

---

## Summary: Where to Look For...

### Plugin Management

- **Add/modify plugins**: `app/src/Module/Velox/Plugin/PluginsBootloader.php`
- **Plugin retrieval logic**: `app/src/Module/Velox/Plugin/Service/`
- **Plugin API endpoints**: `app/src/Module/Velox/Plugin/Endpoint/Http/v1/Plugin/`

### Configuration

- **Config generation**: `app/src/Module/Velox/Configuration/Service/ConfigurationGeneratorService.php`
- **TOML serialization**: Same file, `generateToml()` method
- **Dockerfile generation**: Same file, `generateDockerfile()` method
- **Validation**: `app/src/Module/Velox/Configuration/Service/ConfigurationValidatorService.php`

### Binary Building

- **Build orchestration**: `app/src/Module/Velox/BinaryBuilder/Service/BinaryBuilderService.php`
- **Velox CLI wrapper**: `app/src/Module/Velox/BinaryBuilder/Service/VeloxBinaryRunner.php`

### Presets

- **Preset definitions**: `app/src/Module/Velox/Preset/Service/ConfigPresetProvider.php`
- **Preset merging**: `app/src/Module/Velox/Preset/Service/PresetMergerService.php`

### Dependencies

- **Dependency resolution**: `app/src/Module/Velox/Dependency/Service/DependencyResolverService.php`

### HTTP Infrastructure

- **Interceptors**: `app/src/Application/HTTP/Interceptor/`
- **Response formatting**: `app/src/Application/HTTP/Response/`

### Main Entry Point

- **Facade**: `app/src/Module/Velox/ConfigurationBuilder.php`
