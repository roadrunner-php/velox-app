# Velox Module API Endpoints - Detailed Documentation

## Overview

Complete documentation of all HTTP API endpoints in the Velox module, including request/response formats, query
parameters, and form field requirements for frontend implementation.

---

## Plugin Management Endpoints

### 1. GET /v1/plugins - List All Plugins

**Purpose**: Retrieve list of available plugins with filtering and search capabilities

**Route Configuration**:

```php
#[Route(route: 'v1/plugins', name: 'plugin.list', methods: ['GET'], group: 'api')]
```

**Query Parameters**:

| Parameter                                                            | Type   | Required | Description                           | Example Values                                              |
|----------------------------------------------------------------------|--------|----------|---------------------------------------|-------------------------------------------------------------|
| `category`                                                           | string | No       | Filter by plugin category             | `Core`, `HTTP`, `Jobs`, `Logging`, `Metrics`, `gRPC`, `KV`, 
 `Monitoring`, `Network`, `Broadcasting`, `Workflow`, `Observability` |
| `source`                                                             | string | No       | Filter by plugin source               | `official`, `community`                                     |
| `search`                                                             | string | No       | Search in plugin name and description | `http`, `logger`, `redis`                                   |

**Response Structure**:

```json
{
  "data": [
    {
      "name": "http",
      "version": "v5.0.2",
      "owner": "roadrunner-server",
      "repository": "http",
      "repository_url": "https://github.com/roadrunner-server/http",
      "repository_type": "github",
      "source": "official",
      "description": "HTTP server plugin",
      "category": "HTTP",
      "dependencies": [
        "server"
      ],
      "folder": null,
      "replace": null,
      "is_official": true,
      "full_name": "roadrunner-server/http"
    }
  ],
  "meta": {
    "total": 35,
    "statistics": {
      "by_category": {
        "Core": 4,
        "HTTP": 8,
        "Jobs": 7,
        "Logging": 2
      },
      "by_source": {
        "official": 33,
        "community": 2
      },
      "with_dependencies": 25,
      "total_dependencies": 45
    },
    "filters": {
      "available_categories": [
        "Core",
        "HTTP",
        "Jobs",
        "Logging"
      ],
      "available_sources": [
        "official",
        "community"
      ]
    }
  }
}
```

**Frontend Form Requirements**:

- Search input field (text, max 255 chars)
- Category dropdown (populated from API response)
- Source filter radio buttons or dropdown
- Results display with pagination support

---

### 2. GET /v1/plugin/{name} - Get Plugin Details

**Purpose**: Retrieve detailed information about a specific plugin

**Route Configuration**:

```php
#[Route(route: 'v1/plugin/<name>', name: 'plugin.show', methods: ['GET'], group: 'api')]
```

**Path Parameters**:

| Parameter | Type   | Required | Description               |
|-----------|--------|----------|---------------------------|
| `name`    | string | Yes      | Plugin name (exact match) |

**Response Structure** (Success):

```json
{
  "name": "http",
  "version": "v5.0.2",
  "owner": "roadrunner-server",
  "repository": "http",
  "repository_url": "https://github.com/roadrunner-server/http",
  "repository_type": "github",
  "source": "official",
  "description": "HTTP server plugin",
  "category": "HTTP",
  "dependencies": [
    "server"
  ],
  "folder": null,
  "replace": null,
  "is_official": true,
  "full_name": "roadrunner-server/http"
}
```

**Response Structure** (Error):

```json
{
  "error": {
    "message": "Plugin 'invalid-name' not found",
    "code": 404
  }
}
```

---

### 3. GET /v1/plugin/{name}/dependencies - Get Plugin Dependencies

**Purpose**: Retrieve dependency information for a specific plugin

**Route Configuration**:

```php
#[Route(route: 'v1/plugin/<name>/dependencies', name: 'plugin.dependencies', methods: ['GET'], group: 'api')]
```

**Path Parameters**:

| Parameter | Type   | Required | Description |
|-----------|--------|----------|-------------|
| `name`    | string | Yes      | Plugin name |

**Response Structure**:

```json
{
  "resolved_dependencies": [
    {
      "name": "server",
      "version": "v5.0.2",
      "owner": "roadrunner-server",
      "repository": "server",
      "repository_url": "https://github.com/roadrunner-server/server",
      "repository_type": "github",
      "source": "official",
      "description": "Core server functionality",
      "category": "Core",
      "dependencies": [],
      "folder": null,
      "replace": null,
      "is_official": true,
      "full_name": "roadrunner-server/server"
    }
  ],
  "dependency_count": {
    "resolved": 1
  },
  "conflicts": [
    {
      "plugin": "conflicting-plugin",
      "type": "version_conflict",
      "message": "Version conflict detected",
      "severity": "error",
      "conflicting_plugins": [
        "plugin1",
        "plugin2"
      ]
    }
  ],
  "is_valid": true
}
```

**Frontend Form Requirements**:

- Dependency tree visualization
- Conflict warning display
- Auto-include dependencies option

---

### 4. POST /v1/plugins/generate-config - Generate Configuration from Plugins

**Purpose**: Generate RoadRunner configuration from selected plugins

**Route Configuration**:

```php
#[Route(route: 'v1/plugins/generate-config', name: 'plugin.generate-config', methods: ['POST', 'GET'], group: 'api')]
```

**Request Body (POST)**:

```json
{
  "plugins": [
    "server",
    "http",
    "logger",
    "jobs"
  ],
  "format": "toml"
}
```

**Query Parameters (GET)**:

| Parameter   | Type   | Required | Description           | Example Values                         |
|-------------|--------|----------|-----------------------|----------------------------------------|
| `plugins[]` | array  | Yes      | Array of plugin names | `["server", "http", "logger"]`         |
| `format`    | string | No       | Output format         | `toml`, `json`, `dockerfile`, `docker` |

**Request Parameters**:

| Field Name | Type   | Required | Max Length | Validation Rules                                       |
|------------|--------|----------|------------|--------------------------------------------------------|
| `plugins`  | array  | Yes      | N/A        | Must contain valid plugin names, minimum 1 plugin      |
| `format`   | string | No       | 20         | Must be one of: `toml`, `json`, `dockerfile`, `docker` |

**Response** (Content-Type: text/plain):

```toml
# Generated TOML configuration
[roadrunner]
ref = "v2025.1.1"

[log]
level = "info"
mode = "production"

[github]
[github.plugins.server]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "server"

[github.plugins.http]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "http"
```

**Error Response** (400):

```json
{
  "error": "Presets array is required",
  "code": 400
}
```

**Frontend Form Requirements**:

- Multi-select plugin list with search/filter
- Format selection dropdown
- Real-time dependency resolution display
- Configuration preview area
- Download/copy generated config functionality

---

## Plugin Categories Endpoint

### 5. GET /v1/plugins/categories - Get Available Categories

**Purpose**: Retrieve list of all available plugin categories

**Route Configuration**:

```php
#[Route(route: 'v1/plugins/categories', name: 'plugin.categories', methods: ['GET'], group: 'api')]
```

**Response Structure**:

```json
{
  "data": [
    {
      "value": "Core",
      "label": "Core"
    },
    {
      "value": "HTTP",
      "label": "HTTP"
    },
    {
      "value": "Jobs",
      "label": "Jobs"
    }
  ],
  "meta": {
    "total": 11
  }
}
```

---

## Preset Management Endpoints

### 6. GET /v1/presets - List All Presets

**Purpose**: Retrieve list of available configuration presets with filtering

**Route Configuration**:

```php
#[Route(route: 'v1/presets', name: 'preset.list', methods: ['GET'], group: 'api')]
```

**Query Parameters**:

| Parameter  | Type    | Required | Description                       | Example Values    |
|------------|---------|----------|-----------------------------------|-------------------|
| `tags`     | string  | No       | Comma-separated tags filter       | `web,http,basic`  |
| `search`   | string  | No       | Search in preset name/description | `server`, `queue` |
| `official` | boolean | No       | Filter by official status         | `true`, `false`   |

**Response Structure**:

```json
{
  "data": [
    {
      "name": "web-server",
      "display_name": "Web Server",
      "description": "Basic HTTP server with essential middleware for web applications",
      "plugins": [
        "server",
        "logger",
        "http",
        "headers",
        "gzip",
        "static",
        "fileserver",
        "status"
      ],
      "plugin_count": 8,
      "tags": [
        "web",
        "http",
        "basic"
      ],
      "is_official": true,
      "priority": 10
    }
  ],
  "meta": {
    "total": 9,
    "filters": {
      "available_tags": [
        "web",
        "http",
        "basic",
        "jobs",
        "queue",
        "workflow"
      ]
    }
  }
}
```

**Frontend Form Requirements**:

- Search input field
- Tags filter (multi-select or comma-separated input)
- Official/community filter toggle
- Preset cards with plugin count and descriptions

---

### 7. POST /v1/presets/generate-config - Generate Configuration from Presets

**Purpose**: Generate RoadRunner configuration from selected presets

**Route Configuration**:

```php
#[Route(route: 'v1/presets/generate-config', name: 'preset.generate-config', methods: ['POST', 'GET'], group: 'api')]
```

**Request Body (POST)**:

```json
{
  "presets": [
    "web-server",
    "monitoring"
  ],
  "format": "toml"
}
```

**Query Parameters (GET)**:

| Parameter   | Type   | Required | Description                   |
|-------------|--------|----------|-------------------------------|
| `presets[]` | array  | Yes      | Array of preset names         |
| `format`    | string | No       | Output format (default: toml) |

**Request Parameters**:

| Field Name | Type   | Required | Max Length | Validation Rules                                       |
|------------|--------|----------|------------|--------------------------------------------------------|
| `presets`  | array  | Yes      | N/A        | Must contain valid preset names, minimum 1 preset      |
| `format`   | string | No       | 20         | Must be one of: `toml`, `json`, `dockerfile`, `docker` |

**Response** (Success - Content-Type: text/plain):

```toml
# Generated configuration from presets: web-server, monitoring
[roadrunner]
ref = "v2025.1.1"

[log]
level = "info"
mode = "production"

[github]
[github.plugins.server]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "server"

[github.plugins.metrics]
ref = "v5.0.2"
owner = "roadrunner-server"
repository = "metrics"
```

**Error Response** (400):

```json
{
  "error": "Presets array is required",
  "code": 400
}
```

**Error Response** (422):

```json
{
  "error": "Preset validation failed: Preset 'invalid-preset' not found",
  "code": 422
}
```

**Frontend Form Requirements**:

- Multi-select preset list with descriptions
- Format selection radio buttons or dropdown
- Preset combination validation
- Preview of merged plugins before generation
- Warning display for preset conflicts

---

## API Endpoint Summary Table

| Endpoint                         | Method   | Purpose                      | Key Form Fields Required         |
|----------------------------------|----------|------------------------------|----------------------------------|
| `/v1/plugins`                    | GET      | List plugins with filters    | search, category, source filters |
| `/v1/plugin/{name}`              | GET      | Get plugin details           | plugin name parameter            |
| `/v1/plugin/{name}/dependencies` | GET      | Get dependencies             | plugin name parameter            |
| `/v1/plugins/generate-config`    | POST/GET | Generate config from plugins | plugins array, format selection  |
| `/v1/plugins/categories`         | GET      | Get categories list          | No form fields (data source)     |
| `/v1/presets`                    | GET      | List presets with filters    | search, tags, official filters   |
| `/v1/presets/generate-config`    | POST/GET | Generate config from presets | presets array, format selection  |

## Common Response Patterns

### Success Response Structure

- **Data**: Main response content
- **Meta**: Pagination, totals, statistics, filter options
- **Relationships**: Related data (dependencies, conflicts)

### Error Response Structure

- **Error**: Error message and code
- **Validation**: Field-specific validation errors
- **Context**: Additional error context

### Content Types

- **JSON**: Standard API responses with structured data
- **text/plain**: Generated configuration files (TOML, Dockerfile)

## Authentication & Authorization

**Note**: The current codebase doesn't show explicit authentication middleware, but token management is handled through:

- GitHub tokens via `${RT_TOKEN}` placeholder in generated configs
- Environment variable configuration for `GITHUB_TOKEN`

## Rate Limiting Considerations

- GitHub API calls are made for version checking and repository validation
- Consider implementing caching for plugin/preset data
- Token usage for authenticated GitHub API requests

## Frontend Implementation Recommendations

### Form Field Types Needed:

1. **Search Inputs**: Text fields with debounced search
2. **Multi-Select**: For plugins and presets selection
3. **Dropdowns**: For categories, sources, formats
4. **Checkboxes**: For boolean filters (official status)
5. **Radio Buttons**: For exclusive selections (format)
6. **Text Areas**: For displaying generated configurations

### UX Considerations:

1. **Real-time validation**: Check plugin/preset names as user types
2. **Dependency visualization**: Show plugin relationships
3. **Preview functionality**: Show config before generation
4. **Copy/download options**: Easy access to generated content
5. **Error handling**: Clear error messages for validation failures
6. **Loading states**: Progress indicators for API calls