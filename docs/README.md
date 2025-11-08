# Velox Application Documentation

Welcome to the comprehensive documentation for the RoadRunner Velox Application. This documentation provides a complete
understanding of the application architecture, modules, and development workflows.

## What is Velox Application?

The Velox Application is a sophisticated tool for building custom RoadRunner binaries from a curated selection of
plugins. It provides:

- **Plugin Management**: Catalog of 50+ official and community RoadRunner plugins
- **Preset System**: Predefined plugin combinations for common use cases
- **Dependency Resolution**: Automatic resolution of plugin dependencies
- **Configuration Generation**: TOML and Dockerfile generation for builds
- **Binary Building**: Direct integration with Velox binary builder
- **HTTP API**: RESTful API for all operations
- **Console Commands**: CLI tools for build automation

## Documentation Structure

This documentation is organized into four main sections:

### 1. [Architecture Overview](./architecture-overview.md)

**Start here for a high-level understanding of the system.**

- System architecture and layers
- Core components overview
- Module descriptions
- Design patterns used
- Technology stack
- Extension points
- Request flow diagrams

**Best for**: Architects, team leads, and developers new to the project.

### 2. [Module Reference](./module-reference.md)

**Detailed technical reference for all modules.**

- Complete file locations
- Class purposes and responsibilities
- Method signatures and examples
- DTOs and data structures
- Service implementations
- HTTP endpoint documentation
- Console command reference

**Best for**: Developers implementing features or fixing bugs.

### 3. [Data Flow Guide](./data-flow.md)

**Step-by-step data transformations through the system.**

- Building binaries from plugins
- Building binaries from presets
- HTTP API request flows
- Dependency resolution process
- Configuration generation
- Docker build process
- Error handling flows

**Best for**: Debugging issues, understanding system behavior, and optimization.

### 4. [Development Guide](./development-guide.md)

**Practical guide for developing new features.**

- Adding new plugins
- Creating presets
- Building HTTP endpoints
- Writing console commands
- Extending plugin providers
- Custom dependency resolution
- Testing guidelines
- Code style conventions

**Best for**: Developers actively working on the codebase.

## Quick Start

### For New Developers

1. **Read**: [Architecture Overview](./architecture-overview.md) - Understand the system
2. **Explore**: [Module Reference](./module-reference.md) - Find specific modules
3. **Code**: [Development Guide](./development-guide.md) - Start building features

### For Bug Fixes

1. **Identify**: Use [Module Reference](./module-reference.md) to locate the relevant code
2. **Understand**: Read [Data Flow Guide](./data-flow.md) for the affected flow
3. **Fix**: Follow [Development Guide](./development-guide.md) for best practices

### For Feature Development

1. **Plan**: Review [Architecture Overview](./architecture-overview.md) for extension points
2. **Design**: Follow patterns in [Development Guide](./development-guide.md)
3. **Implement**: Use [Module Reference](./module-reference.md) for API details
4. **Test**: Follow testing guidelines in [Development Guide](./development-guide.md)

## Key Concepts

### Application Layers

```
Application Layer
  ↓ HTTP Infrastructure, Interceptors, Exception Handling
Business Logic Layer
  ↓ Velox Module (Plugins, Configuration, Building, etc.)
Infrastructure Layer
  ↓ Spiral Framework, RoadRunner, External APIs
```

### Main Modules

1. **Plugin**: Manages RoadRunner plugins catalog
2. **Configuration**: Generates and validates Velox configurations
3. **BinaryBuilder**: Builds RoadRunner binaries
4. **Preset**: Manages predefined plugin combinations
5. **Dependency**: Resolves plugin dependencies
6. **Version**: Checks and manages plugin versions
7. **Environment**: Manages environment files

### Central Facade

`ConfigurationBuilder` (`app/src/Module/Velox/ConfigurationBuilder.php`) is the main entry point for all Velox
operations. It coordinates between all modules and provides a unified API.

## Common Tasks

### Adding a New Plugin

**File**: `app/src/Module/Velox/Plugin/PluginsBootloader.php`

```php
new Plugin(
    name: 'my-plugin',
    ref: $env->get('RR_PLUGIN_MY_PLUGIN', 'v1.0.0'),
    owner: 'roadrunner-server',
    repository: 'my-plugin',
    repositoryType: PluginRepository::Github,
    source: PluginSource::Official,
    dependencies: ['logger'],
    description: 'My plugin description',
    category: PluginCategory::Core,
)
```

**See**: [Development Guide - Adding Plugins](./development-guide.md#1-adding-new-plugins)

### Creating a Preset

**File**: `app/src/Module/Velox/Preset/Service/ConfigPresetProvider.php`

```php
new PresetDefinition(
    name: 'web-server',
    description: 'Complete web server setup',
    pluginNames: ['server', 'http', 'logger', 'headers', 'gzip'],
    tags: ['web', 'production'],
    priority: 10,
)
```

**See**: [Development Guide - Creating Presets](./development-guide.md#2-creating-new-presets)

### Adding HTTP Endpoint

1. Create Action: `app/src/Module/Velox/*/Endpoint/Http/v1/*/MyAction.php`
2. Create Filter: `MyActionFilter.php`
3. Create Resource: `MyResource.php`

**See**: [Development Guide - HTTP Endpoints](./development-guide.md#3-adding-http-endpoints)

## Architecture Highlights

### Design Patterns

- **Facade Pattern**: `ConfigurationBuilder` simplifies complex subsystem
- **Provider Pattern**: Flexible plugin/preset sources
- **Repository Pattern**: Data access abstraction
- **Service Layer**: Encapsulated business logic
- **DTO Pattern**: Type-safe data transfer

### Technology Stack

- **PHP 8.3+**: Modern PHP with strict typing
- **Spiral Framework**: PSR-compliant framework
- **Velox**: RoadRunner binary builder
- **TOML**: Configuration format
- **OpenAPI**: API documentation

## File Structure

```
app/src/
├── Application/          # HTTP infrastructure
│   ├── Bootloader/
│   ├── HTTP/
│   │   ├── Interceptor/
│   │   └── Response/
│   └── Exception/
│
├── Module/
│   ├── Velox/           # Core business logic
│   │   ├── Plugin/
│   │   ├── Configuration/
│   │   ├── BinaryBuilder/
│   │   ├── Preset/
│   │   ├── Dependency/
│   │   ├── Version/
│   │   ├── Environment/
│   │   └── ConfigurationBuilder.php  # Main facade
│   │
│   └── Github/          # GitHub integration
│       └── Contributors/
│
docs/                    # Documentation (this)
├── architecture-overview.md
├── module-reference.md
├── data-flow.md
├── development-guide.md
└── README.md
```

## API Endpoints

### Plugins

- `GET /api/v1/plugins` - List all plugins
- `GET /api/v1/plugins/{name}` - Get plugin details
- `GET /api/v1/plugins/categories` - List categories
- `POST /api/v1/plugins/dependencies` - Resolve dependencies
- `POST /api/v1/plugins/generate-config` - Generate configuration

### Presets

- `GET /api/v1/presets` - List presets
- `POST /api/v1/presets/generate-config` - Generate from presets

### Contributors

- `GET /api/v1/contributors` - List GitHub contributors

**See**: [Module Reference](./module-reference.md) for complete API documentation.

## Console Commands

```bash
# Build binary from plugins
php app.php velox:build --plugins=http,logger,server

# Generate from presets
php app.php velox:preset:generate --presets=web-server

# Check for plugin updates
php app.php velox:version:update
```

**See**: [Module Reference](./module-reference.md) for all commands.

## Development Workflow

1. **Setup**: Clone repository, install dependencies
2. **Explore**: Read this documentation
3. **Develop**: Follow patterns in Development Guide
4. **Test**: Write unit and integration tests
5. **Review**: Code review and quality checks
6. **Deploy**: Merge and deploy

## Testing

```bash
# Run all tests
composer test

# Check code style
composer cs:fix

# Static analysis
composer psalm

# Refactor
composer refactor
```

## Additional Resources

- **Spiral Framework**: https://spiral.dev/docs
- **RoadRunner**: https://roadrunner.dev/docs
- **Velox**: https://github.com/roadrunner-server/velox

## Contributing

When contributing to this project:

1. Read the [Development Guide](./development-guide.md)
2. Follow code style conventions
3. Write tests for new features
4. Update documentation
5. Submit pull request

## Support

- **GitHub Issues**: Report bugs and request features
- **Documentation**: Start with [Architecture Overview](./architecture-overview.md)
- **Code Examples**: See [Development Guide](./development-guide.md)

---

## Documentation Index

| Document                                            | Purpose                        | Target Audience                        |
|-----------------------------------------------------|--------------------------------|----------------------------------------|
| [Architecture Overview](./architecture-overview.md) | High-level system architecture | Architects, Team Leads, New Developers |
| [Module Reference](./module-reference.md)           | Detailed technical reference   | All Developers                         |
| [Data Flow Guide](./data-flow.md)                   | Data transformation flows      | Developers, QA, DevOps                 |
| [Development Guide](./development-guide.md)         | Practical development guide    | Active Developers                      |

Start with the document that best matches your needs, and refer to others as necessary. Happy coding! 🚀
