# RoadRunner Velox Application - Architecture Overview

## Introduction

The Velox Application is a sophisticated tool designed to build custom RoadRunner binaries from a curated list of
plugins. It provides both HTTP API and console interfaces for selecting plugins, managing presets, resolving
dependencies, and ultimately assembling custom RoadRunner binaries.

## High-Level Architecture

The application follows a modular, domain-driven design built on top of the Spiral Framework. It's organized into three
main layers:

```
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                       │
│  (HTTP Infrastructure, Interceptors, Exception Handling)    │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│                      Business Logic Layer                    │
│              (Velox Module & Github Module)                  │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│                    Infrastructure Layer                      │
│        (Spiral Framework, RoadRunner, External APIs)         │
└─────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. Application Layer (`app/src/Application`)

Provides the foundational HTTP and exception handling infrastructure for the entire application.

**Key Responsibilities:**

- HTTP request/response handling
- Exception interception and formatting
- Parameter conversion and validation
- JSON resource serialization

**Main Components:**

- **Bootloaders**: Initialize core application services
    - `AppBootloader`: Sets up domain interceptors
    - `ExceptionHandlerBootloader`: Configures exception handling
    - `LoggingBootloader`: Configures application logging

- **HTTP Interceptors**: Request/response pipeline processors
    - `ExceptionHandlerInterceptor`: Catches and formats exceptions
    - `JsonResourceInterceptor`: Converts resources to JSON responses
    - `UuidParametersConverterInterceptor`: Converts UUID string parameters
    - `StringToIntParametersInterceptor`: Type conversion for route parameters

- **Response Abstractions**: Standardized response formats
    - `ResourceInterface`: Base interface for all API resources
    - `JsonResource`: Generic JSON response wrapper
    - `ErrorResource`: Error response formatting
    - `ValidationResource`: Validation error responses

### 2. Velox Module (`app/src/Module/Velox`)

The core business domain containing all RoadRunner binary building logic. This module is divided into specialized
sub-modules:

#### a. **Plugin Sub-module** (`Module/Velox/Plugin`)

Manages the catalog of available RoadRunner plugins (both official and community).

**Key Components:**

- **DTOs**: `Plugin`, `PluginMetadata`, `PluginRepository`, `PluginSource`, `PluginCategory`
- **Services**:
    - `PluginProviderInterface`: Contract for plugin retrieval
    - `ConfigPluginProvider`: Provides plugins from configuration
    - `CompositePluginProvider`: Combines multiple plugin providers
- **Endpoints**:
    - HTTP: `/api/v1/plugins` - List, search, and filter plugins
    - HTTP: `/api/v1/plugins/{name}` - Get single plugin details
    - HTTP: `/api/v1/plugins/categories` - List plugin categories
    - HTTP: `/api/v1/plugins/dependencies` - Resolve plugin dependencies
- **Bootloader**: `PluginsBootloader` - Initializes 50+ core and community plugins

**Plugin Categories:**

- Core, Logging, HTTP, Jobs, KV, Metrics, gRPC, Monitoring, Network, Broadcasting, Workflow, Observability

#### b. **Configuration Sub-module** (`Module/Velox/Configuration`)

Handles Velox configuration generation, validation, and serialization.

**Key Components:**

- **DTOs**:
    - `VeloxConfig`: Main configuration object
    - `RoadRunnerConfig`: RoadRunner version configuration
    - `GitHubConfig`, `GitLabConfig`: Repository access configuration
    - `LogConfig`, `DebugConfig`: Build logging configuration
    - `ValidationResult`: Configuration validation results

- **Services**:
    - `ConfigurationGeneratorService`:
        - Builds VeloxConfig from plugin selections
        - Generates TOML configuration files
        - Generates Dockerfiles for containerized builds
    - `ConfigurationValidatorService`: Validates configuration integrity

**Outputs:**

- TOML configuration files (for Velox binary builder)
- Dockerfiles (multi-stage builds with Velox)

#### c. **BinaryBuilder Sub-module** (`Module/Velox/BinaryBuilder`)

Orchestrates the actual RoadRunner binary compilation process.

**Key Components:**

- **DTOs**: `BuildResult` - Contains build output, timing, size, logs
- **Services**:
    - `BinaryBuilderService`: Main build orchestration
        - Validates configuration
        - Prepares build directories
        - Executes Velox binary builder
        - Handles cleanup
    - `VeloxBinaryRunner`: Wrapper around `vx` CLI tool
        - Executes `vx build` commands
        - Manages timeouts
        - Checks Velox availability

- **Endpoints**:
    - Console: `BuildBinary` - CLI command for building binaries

**Build Process:**

1. Validate configuration
2. Generate TOML config
3. Create temporary build directory
4. Execute `vx build -c velox.toml -o {output}`
5. Verify binary creation
6. Cleanup temporary files
7. Return build result with metrics

#### d. **Preset Sub-module** (`Module/Velox/Preset`)

Manages predefined plugin combinations for common use cases.

**Key Components:**

- **DTOs**:
    - `PresetDefinition`: Named collection of plugins with priority
    - `PresetMergeResult`: Result of merging multiple presets
    - `PresetValidationResult`: Preset compatibility validation

- **Services**:
    - `PresetProviderInterface`: Contract for preset retrieval
    - `ConfigPresetProvider`: Provides presets from configuration
    - `PresetMergerService`: Merges multiple presets intelligently
    - `PresetValidatorService`: Validates preset combinations

- **Endpoints**:
    - HTTP: `/api/v1/presets` - List available presets
    - HTTP: `/api/v1/presets/generate` - Generate config from presets
    - Console: `GenerateFromPresets` - CLI preset-based generation

**Features:**

- Priority-based merging (higher priority presets override)
- Conflict detection (job drivers, KV storage overlap)
- Preset recommendations based on selected plugins

#### e. **Dependency Sub-module** (`Module/Velox/Dependency`)

Resolves plugin dependencies and detects conflicts.

**Key Components:**

- **DTOs**:
    - `DependencyResolution`: Complete dependency graph with conflicts
    - `ConflictInfo`: Detailed conflict information
    - `ConflictType`: Enum (CircularDependency, MissingDependency, VersionConflict, ResourceConflict)
    - `ConflictSeverity`: Enum (Error, Warning)
    - `VersionSuggestion`: Recommendations for version upgrades

- **Services**:
    - `DependencyResolverService`:
        - Recursive dependency resolution
        - Circular dependency detection
        - Version conflict detection
        - Resource conflict warnings
        - Compatible version suggestions

**Dependency Resolution Algorithm:**

1. Depth-first recursive traversal
2. Visited tracking for circular dependency detection
3. Automatic inclusion of transitive dependencies
4. Duplicate removal
5. Conflict analysis (version, resource, compatibility)

#### f. **Version Sub-module** (`Module/Velox/Version`)

Manages plugin version checking and updates.

**Key Components:**

- **DTOs**: `VersionUpdateInfo` - Available updates information
- **Services**:
    - `GitHubVersionChecker`: Fetches latest versions from GitHub API
    - `PluginVersionManager`: Manages plugin version updates
    - `VersionComparisonService`: Semantic version comparison

- **Endpoints**:
    - Console: `UpdatePluginVersions` - CLI command to check/update versions

#### g. **Environment Sub-module** (`Module/Velox/Environment`)

Handles environment file operations.

**Key Components:**

- **Services**: `EnvironmentFileService` - Read/write .env files
- **Exceptions**: `EnvironmentFileException`

#### h. **ConfigurationBuilder** (Facade)

The main entry point and facade for all Velox functionality. It coordinates between all sub-modules.

**Located at**: `app/src/Module/Velox/ConfigurationBuilder.php`

**Public API Methods:**

**Plugin Management:**

- `getAvailablePlugins(): array<Plugin>`
- `getPluginsByCategory(PluginCategory): array<Plugin>`
- `searchPlugins(string $query): array<Plugin>`

**Configuration Building:**

- `buildConfiguration(array $pluginNames, ?string $githubToken): VeloxConfig`
- `buildConfigurationFromPresets(array $presetNames, ?string $githubToken): VeloxConfig`
- `validateConfiguration(VeloxConfig): ValidationResult`

**Preset Management:**

- `getAvailablePresets(): array<PresetDefinition>`
- `getPresetsByTags(array $tags): array<PresetDefinition>`
- `searchPresets(string $query): array<PresetDefinition>`
- `validatePresets(array $presetNames): PresetValidationResult`
- `mergePresets(array $presetNames): PresetMergeResult`

**Dependency Resolution:**

- `resolveDependencies(array $pluginNames): DependencyResolution`

**Output Generation:**

- `generateToml(VeloxConfig): string`
- `generateDockerfile(VeloxConfig, string $baseImage): string`

**Binary Building:**

- `buildBinary(VeloxConfig, string $outputDir): BuildResult`

### 3. Github Module (`app/src/Module/Github`)

Provides GitHub integration for fetching repository contributors.

**Key Components:**

- **Client**: GitHub API client wrapper
- **Contributors Sub-module**:
    - `GithubContributorRepository`: Fetches contributors from GitHub
    - `CachedContributors`: Caching layer for contributor data
    - `ContributorsRepositoryInterface`: Repository contract
    - DTOs: `Contributor`
    - Endpoints: `/api/v1/contributors` - List project contributors

## Request Flow

### HTTP API Request Flow

```
HTTP Request
    ↓
[AppBootloader Interceptors]
    ↓
StringToIntParametersInterceptor (convert string params to int)
    ↓
UuidParametersConverterInterceptor (convert UUID strings)
    ↓
[Route Handler / Action]
    ↓
ConfigurationBuilder (facade)
    ↓
[Sub-module Services]
    ↓
JsonResourceInterceptor (convert to JSON)
    ↓
ExceptionHandlerInterceptor (catch errors)
    ↓
HTTP Response
```

### Binary Building Flow

```
Plugin Selection (User Input)
    ↓
[Preset Merging] (if using presets)
    ↓
PresetMergerService.mergePresets()
    ↓
[Dependency Resolution]
    ↓
DependencyResolverService.resolveDependencies()
    ↓
[Configuration Generation]
    ↓
ConfigurationGeneratorService.buildConfigFromSelection()
    ↓
[Configuration Validation]
    ↓
ConfigurationValidatorService.validateConfiguration()
    ↓
[Binary Building]
    ↓
BinaryBuilderService.buildBinary()
    ↓
VeloxBinaryRunner.build() → executes: vx build -c velox.toml
    ↓
BuildResult (binary path, logs, metrics)
```

## Key Design Patterns

### 1. **Facade Pattern**

`ConfigurationBuilder` serves as a facade, providing a simplified interface to the complex Velox subsystem.

### 2. **Provider Pattern**

`PluginProviderInterface` and `PresetProviderInterface` allow flexible plugin/preset sources.

### 3. **Composite Pattern**

`CompositePluginProvider` combines multiple plugin providers into a single interface.

### 4. **DTO Pattern**

Extensive use of Data Transfer Objects for type-safe data handling across boundaries.

### 5. **Repository Pattern**

Used in the Github module for data access abstraction.

### 6. **Service Layer Pattern**

Business logic encapsulated in service classes with single responsibilities.

### 7. **Bootloader Pattern** (Spiral Framework)

Application initialization through bootloaders that define singletons and dependencies.

## Technology Stack

- **Framework**: Spiral Framework (PSR-compliant PHP framework)
- **Language**: PHP 8.3+
- **Binary Builder**: Velox (vx CLI tool)
- **Configuration Format**: TOML
- **API Documentation**: OpenAPI/Swagger
- **Containerization**: Docker (multi-stage builds)

## Extension Points

To extend the application, consider these key extension points:

1. **Add New Plugins**:
    - Modify `PluginsBootloader.php`
    - Add new `Plugin` instances to `initCorePlugins()` or `initCommonPlugins()`

2. **Add New Presets**:
    - Implement `PresetProviderInterface`
    - Register in `PresetBootloader`

3. **Add New Plugin Providers**:
    - Implement `PluginProviderInterface`
    - Add to `CompositePluginProvider` in `PluginsBootloader`

4. **Add New HTTP Endpoints**:
    - Create action classes in appropriate `Endpoint/Http/v1/` directory
    - Use `#[Route]` attribute for routing
    - Use OpenAPI attributes for documentation

5. **Add New Console Commands**:
    - Create command classes in `Endpoint/Console/`
    - Extend Spiral's console command base

## Next Steps

For detailed information on specific modules, see:

- [Module Reference](./module-reference.md) - Detailed breakdown of each module
- [Data Flow Guide](./data-flow.md) - Step-by-step data transformations
- [Development Guide](./development-guide.md) - How to develop new features
