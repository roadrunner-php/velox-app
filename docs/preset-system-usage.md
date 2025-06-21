# Velox Preset System

The Velox Preset System allows you to quickly configure RoadRunner servers using predefined plugin combinations for common use cases.

## Available Presets

### Core Presets

- **web-server**: Basic HTTP server with essential middleware
- **queue-server**: Job processing server with multiple queue drivers  
- **workflow-server**: Temporal workflow engine setup
- **api-server**: REST API server with JSON handling
- **microservices**: Service mesh ready with gRPC

### Specialized Presets

- **k8s**: Kubernetes-optimized with health checks and observability
- **monitoring**: Enhanced monitoring and observability stack
- **security**: Security-focused configuration
- **broadcasting**: Real-time communication with Centrifuge
- **minimal**: Lightweight setup with only essentials
- **full-stack**: Complete RoadRunner setup
- **development**: Development-friendly with debug features

## Usage Examples

### Command Line

```bash
# List all available presets
./rr velox:generate-from-presets --list

# Generate config from single preset
./rr velox:generate-from-presets --presets=web-server

# Merge multiple presets
./rr velox:generate-from-presets --presets=web-server --presets=monitoring --presets=k8s

# Validate preset combination
./rr velox:generate-from-presets --presets=web-server --presets=queue-server --validate
```

### PHP Code

```php
use App\Module\Velox\ConfigurationBuilder;

// Build configuration from presets
$config = $builder->buildConfigurationFromPresets(['web-server', 'monitoring']);

// Validate preset combination
$validation = $builder->validatePresets(['web-server', 'queue-server', 'k8s']);

// Get merge details
$mergeResult = $builder->mergePresets(['api-server', 'security']);

// Get recommended presets based on selected plugins
$recommendations = $builder->getRecommendedPresets(['http', 'jobs', 'redis']);
```

## Preset Definitions

Each preset contains:
- **name**: Unique identifier
- **displayName**: Human-readable name
- **description**: What the preset is for
- **pluginNames**: List of plugins to include
- **tags**: Categorization tags
- **priority**: Merge priority (higher = takes precedence)

## Common Combinations

### Web Application Stack
```bash
./rr velox:generate-from-presets --presets=web-server --presets=monitoring
```

### Queue Processing System
```bash
./rr velox:generate-from-presets --presets=queue-server --presets=monitoring --presets=k8s
```

### Microservices Architecture
```bash
./rr velox:generate-from-presets --presets=microservices --presets=monitoring --presets=security
```

### Full Production Setup
```bash
./rr velox:generate-from-presets --presets=web-server --presets=queue-server --presets=monitoring --presets=security --presets=k8s
```

## Preset Validation

The system automatically:
- Validates preset combinations for conflicts
- Provides warnings for potential issues
- Suggests recommendations for better configurations
- Handles plugin dependencies automatically

## Adding Custom Presets

Add new presets in `PresetBootloader::initPresets()`:

```php
new PresetDefinition(
    name: 'my-custom-preset',
    displayName: 'My Custom Preset',
    description: 'Custom preset for specific use case',
    pluginNames: ['server', 'logger', 'http'],
    tags: ['custom', 'specific'],
    priority: 10,
),
```
