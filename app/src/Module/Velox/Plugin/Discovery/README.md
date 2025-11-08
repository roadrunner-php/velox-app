# Community Plugin Auto-Discovery System

Automatically discovers and registers RoadRunner community plugins from the `roadrunner-plugins` GitHub organization.

## Features

- 🔍 **Automatic Discovery**: Scans GitHub organization for plugins with `.velox.yaml` manifests
- 🔄 **Real-time Updates**: GitHub webhook integration for instant plugin updates
- 📦 **In-Memory Storage**: Fast ArrayPluginRepository for discovered plugins
- 🎯 **Priority System**: Community plugins can override official plugins
- ⚡ **Lazy Loading**: Scan on first API call, not on application boot
- 🛡️ **Validation**: Comprehensive manifest validation with clear error messages

## Quick Start

### 1. Configuration

Copy the environment configuration:

```bash
cp .env.discovery.example .env
```

Update these variables:

```env
# Required: GitHub Personal Access Token
GITHUB_TOKEN=your_github_token_here

# Optional: Webhook secret for security
VELOX_WEBHOOK_SECRET=your_secure_secret_here

# Optional: Custom organization (default: roadrunner-plugins)
VELOX_GITHUB_ORG=roadrunner-plugins
```

### 2. Enable Discovery

Add `DiscoveryBootloader` to your application bootloaders in `app/config/app.php`:

```php
return [
    'bootloaders' => [
        // ... existing bootloaders
        \App\Module\Velox\Plugin\Discovery\DiscoveryBootloader::class,
    ],
];
```

### 3. Discover Plugins

Run the discovery command:

```bash
php app.php velox:discover-plugins
```

Output:
```
Discovering community plugins from roadrunner-plugins...

✓ Discovery completed!

┌───────────────────────┬────────┐
│ Metric                │ Value  │
├───────────────────────┼────────┤
│ Repositories Scanned  │ 12     │
│ Plugins Registered    │ 10     │
│ Plugins Failed        │ 2      │
│ Duration              │ 3.45s  │
└───────────────────────┴────────┘

Registered Plugins:
  - redis-cache (v1.2.0) [kv]
  - http-rate-limiter (v2.0.1) [http]
  - azure-queue-driver (v1.0.0) [jobs]
  ...
```

## Plugin Manifest (`.velox.yaml`)

Each plugin repository must contain a `.velox.yaml` file:

```yaml
# Required fields
name: redis-cache
version: v1.2.0
owner: roadrunner-plugins
repository: redis-cache
description: "Redis-based caching plugin for RoadRunner with TTL support"
category: kv
dependencies:
  - logger
  - kv
  - redis

# Optional fields
repositoryType: github
folder: null
replace: null
docsUrl: https://github.com/roadrunner-plugins/redis-cache
author:
  name: John Doe
  email: john@example.com
  url: https://github.com/johndoe
license: MIT
keywords:
  - cache
  - redis
  - performance
```

### Available Categories

- `core` - Core functionality
- `logging` - Logging plugins
- `http` - HTTP server and middleware
- `jobs` - Job queue systems
- `kv` - Key-value storage
- `metrics` - Metrics and monitoring
- `grpc` - gRPC server
- `monitoring` - Health checks and status
- `network` - Network protocols
- `broadcasting` - WebSocket/broadcasting
- `workflow` - Workflow engines
- `observability` - Tracing and observability

### Validation Rules

**Name**: 
- Pattern: `^[a-z0-9-]{3,50}$`
- Lowercase alphanumeric with hyphens, 3-50 characters

**Version**: 
- Pattern: `^v\d+\.\d+\.\d+$`
- Must match GitHub release tag (e.g., `v1.2.3`)

**Description**: 
- Length: 10-500 characters

**Category**: 
- Must be one of the available categories

**Dependencies**: 
- Array of valid plugin names

## HTTP API

### List Discovered Plugins

```http
GET /api/v1/plugins/discovered
```

Response:
```json
{
  "data": [
    {
      "name": "redis-cache",
      "version": "v1.2.0",
      "description": "Redis-based caching plugin",
      "category": "kv",
      "source": "community",
      "dependencies": ["logger", "kv", "redis"],
      "docs_url": "https://github.com/roadrunner-plugins/redis-cache"
    }
  ],
  "meta": {
    "total": 10,
    "last_scan": "2025-01-15T10:30:00Z",
    "scan_duration_ms": 3450
  }
}
```

### Trigger Manual Scan

```http
POST /api/v1/plugins/discovery/scan
Authorization: Bearer {token}
```

Response:
```json
{
  "status": "success",
  "statistics": {
    "repositories_scanned": 12,
    "plugins_registered": 10,
    "plugins_failed": 2,
    "duration_ms": 3450
  }
}
```

## GitHub Webhook Integration

### Setup Webhook

1. Go to your GitHub organization settings: `https://github.com/organizations/roadrunner-plugins/settings/hooks`
2. Click "Add webhook"
3. Configure:
   - **Payload URL**: `https://your-domain.com/api/v1/plugins/discovery/webhook`
   - **Content type**: `application/json`
   - **Secret**: Your `VELOX_WEBHOOK_SECRET` value
   - **Events**: Select "Releases" only
   - **Active**: ✓

### Webhook Payload

When a release is published, GitHub sends:

```json
{
  "action": "published",
  "release": {
    "tag_name": "v1.2.0",
    "draft": false,
    "prerelease": false
  },
  "repository": {
    "name": "redis-cache",
    "owner": {
      "login": "roadrunner-plugins"
    }
  }
}
```

Response:
```json
{
  "status": "success",
  "plugin": {
    "name": "redis-cache",
    "version": "v1.2.0",
    "updated": true
  }
}
```

## CLI Commands

### Discover Plugins

```bash
php app.php velox:discover-plugins [options]
```

**Options:**
- `--force, -f` - Force re-scan even if cache is valid
- `--clear-cache, -c` - Clear existing cache before scan

## Architecture

### Plugin Priority System

The `CompositePluginProvider` uses a priority system:

1. **Priority 1**: Discovered community plugins (can override official)
2. **Priority 2**: Official and manually configured plugins

This allows community plugins to override official ones when needed.

### Lazy Loading

By default, discovery runs on the first API call, not on application boot:

```env
VELOX_LAZY_LOAD=true
```

This prevents slow application startup while still keeping plugins fresh.

### Storage

Uses `ArrayPluginRepository` for in-memory storage:
- Fast access
- No external dependencies
- Simple implementation
- Perfect for discovered plugins

## Error Handling

### Validation Errors

Invalid manifests are logged and skipped:

```
⚠ Manifest validation failed
Repository: invalid-plugin
Error: Missing required field: description
```

### GitHub API Errors

Rate limiting is handled gracefully:

```
✗ GitHub API rate limit exceeded
Repository: some-plugin
Resets at: 2025-01-15 11:00:00
```

### Failed Repositories

Failed repositories are tracked in statistics:

```json
{
  "failed_repositories": {
    "invalid-plugin": "Missing required field: description",
    "broken-manifest": "Invalid YAML syntax"
  }
}
```

## Testing

Test discovery manually:

```bash
# Discover with verbose output
php app.php velox:discover-plugins -v

# Force re-scan
php app.php velox:discover-plugins --force

# Clear cache and scan
php app.php velox:discover-plugins --clear-cache
```

## Security

### Webhook Authentication

Simple secret-based authentication:

```php
// Request header
X-Velox-Secret: your_secret_here

// Validated against
VELOX_WEBHOOK_SECRET=your_secret_here
```

### GitHub Token

Use a GitHub Personal Access Token with minimal permissions:
- ✓ `public_repo` - Read public repositories
- ✗ No write permissions needed

## Troubleshooting

### No plugins discovered

Check:
1. `GITHUB_TOKEN` is set and valid
2. Organization name is correct: `VELOX_GITHUB_ORG=roadrunner-plugins`
3. Repositories have `.velox.yaml` files
4. Repositories have GitHub releases

### Webhook not working

Check:
1. `VELOX_WEBHOOK_SECRET` matches GitHub webhook secret
2. Endpoint is publicly accessible
3. GitHub webhook shows successful deliveries
4. Check logs for webhook errors

### Rate limiting

Solutions:
1. Set `GITHUB_TOKEN` for authenticated access (5000 requests/hour)
2. Increase cache TTL to reduce API calls
3. Use lazy loading: `VELOX_LAZY_LOAD=true`

## Configuration Reference

| Variable | Default | Description |
|----------|---------|-------------|
| `VELOX_DISCOVERY_ENABLED` | `true` | Enable/disable discovery system |
| `VELOX_GITHUB_ORG` | `roadrunner-plugins` | GitHub organization to scan |
| `VELOX_MANIFEST_FILE` | `.velox.yaml` | Manifest filename |
| `VELOX_LAZY_LOAD` | `true` | Lazy loading on first API call |
| `VELOX_WEBHOOK_SECRET` | - | Webhook authentication secret |
| `GITHUB_TOKEN` | - | GitHub Personal Access Token |
| `GITHUB_TIMEOUT` | `30` | GitHub API timeout (seconds) |
| `GITHUB_RETRY_ATTEMPTS` | `3` | Retry attempts on failure |

## Dependencies

Required:
- `symfony/yaml` - YAML parsing
- PSR-16 SimpleCache - Caching interface
- PSR-18 HTTP Client - GitHub API requests

Install via Composer:
```bash
composer require symfony/yaml
```

## Contributing

To add a plugin to the `roadrunner-plugins` organization:

1. Create repository in `roadrunner-plugins`
2. Add `.velox.yaml` manifest at root
3. Create a GitHub release (e.g., `v1.0.0`)
4. Plugin will be discovered automatically

## License

This discovery system is part of the Velox Application and follows the same license.
