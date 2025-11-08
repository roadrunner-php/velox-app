# Plugin YAML Schema

## Overview

Community plugins in the Roadrunner Plugins GitHub organization must include a `.velox.yaml` file at the root of their
repository. This file describes the plugin metadata and is used by the Velox Application to automatically discover and
register community plugins.

## File Location

```
repository-root/
├── .velox.yaml      # Required
├── README.md
├── go.mod
└── ...
```

## Required Fields

```yaml
# Unique plugin identifier (lowercase, alphanumeric with hyphens)
name: redis-cache

# Current plugin version (semver or git ref)
version: v1.2.0

# GitHub organization or username
owner: roadrunner-plugins

# Repository name
repository: redis-cache

# Human-readable plugin description (10-500 characters)
description: Redis-based caching plugin for RoadRunner with TTL support and automatic invalidation

# Plugin category
category: kv

# List of required plugin names
dependencies:
  - logger
  - kv
  - redis
```

**Available Categories**: `core`, `logging`, `http`, `jobs`, `kv`, `metrics`, `grpc`, `monitoring`, `network`,
`broadcasting`, `workflow`, `observability`

## Optional Fields

```yaml
# Repository hosting platform (default: github)
repositoryType: github  # or gitlab

# URL to plugin documentation
docsUrl: https://github.com/roadrunner-plugins/redis-cache

# Subfolder path if plugin is not at repo root
folder: /plugins/cache

# Go module replacement directive
replace: github.com/old-module=>github.com/new-module

# Author information
author:
  name: RoadRunner Team
  email: team@roadrunner.dev
  url: https://roadrunner.dev

# Keywords for search and discovery
keywords:
  - cache
  - redis
  - performance
  - ttl

# SPDX license identifier
license: MIT

# Minimum required RoadRunner version
minRoadRunnerVersion: v2024.1.0

# Required Go version for building
goVersion: "1.21"
```

## Example Files

### Minimal Example

```yaml
name: redis-cache
version: v1.2.0
owner: roadrunner-plugins
repository: redis-cache
description: Redis-based caching plugin for RoadRunner with TTL support and automatic invalidation
category: kv
dependencies:
  - logger
  - kv
  - redis
```

### Complete Example

```yaml
# Plugin identification
name: redis-cache
version: v1.2.0
owner: roadrunner-plugins
repository: redis-cache
repositoryType: github

# Plugin metadata
description: |
  Redis-based caching plugin for RoadRunner with TTL support,
  automatic invalidation, and cluster support
category: kv

# Dependencies
dependencies:
  - logger
  - kv
  - redis

# Documentation
docsUrl: https://github.com/roadrunner-plugins/redis-cache

# Build configuration
goVersion: "1.21"
minRoadRunnerVersion: v2024.1.0

# Author information
author:
  name: RoadRunner Team
  email: team@roadrunner.dev
  url: https://roadrunner.dev

# Discovery
keywords:
  - cache
  - redis
  - performance
  - ttl

# Legal
license: MIT
```

### HTTP Middleware Example

```yaml
name: rate-limiter
version: v2.0.1
owner: roadrunner-plugins
repository: http-rate-limiter
description: Advanced rate limiting middleware for HTTP with Redis backend and token bucket algorithm
category: http

dependencies:
  - logger
  - http
  - redis

docsUrl: https://github.com/roadrunner-plugins/http-rate-limiter

author:
  name: Community
  url: https://github.com/roadrunner-plugins

keywords:
  - rate-limit
  - http
  - middleware
  - throttle

license: MIT
```

### Job Driver Example

```yaml
name: azure-queue
version: v1.0.0
owner: roadrunner-plugins
repository: azure-queue-driver
description: Azure Queue Storage driver for RoadRunner job system
category: jobs

dependencies:
  - logger
  - jobs

docsUrl: https://github.com/roadrunner-plugins/azure-queue-driver

author:
  name: Azure Community
  email: community@azure.example
  url: https://github.com/roadrunner-plugins

keywords:
  - azure
  - queue
  - jobs
  - cloud

license: Apache-2.0
minRoadRunnerVersion: v2024.1.0
```

## Validation Rules

1. **Name**:
    - Must be unique across all plugins
    - Lowercase alphanumeric with hyphens only
    - No spaces or special characters
    - Pattern: `^[a-z0-9-]+$`

2. **Version**:
    - Must follow semantic versioning (vX.Y.Z) or be a valid git ref
    - Should match the latest git tag in the repository

3. **Description**:
    - Minimum 10 characters
    - Maximum 500 characters
    - Should clearly explain what the plugin does

4. **Category**:
    - Must be one of the predefined categories
    - Used for filtering and organization

5. **Dependencies**:
    - Must reference existing plugin names
    - Will be validated during registration

## Plugin Discovery Process

1. **Repository Organization**: `roadrunner-plugins`
2. **Scan Frequency**: Once per day (configurable via cron)
3. **Discovery Method**: GitHub API to list repositories
4. **Validation**: Each `.velox.yaml` is validated against schema
5. **Registration**: Valid plugins are cached in database
6. **Availability**: Immediately available in plugin list API

### Discovery Algorithm

```
1. Fetch list of repositories from roadrunner-plugins org
2. For each repository:
   a. Check for .velox.yaml file
   b. Download and parse YAML
   c. Validate against schema
   d. Resolve dependencies
   e. Store in database (upsert)
   f. Update cache
3. Clean up removed repositories
4. Log statistics
```

## Error Handling

If a `.velox.yaml` is invalid:

- Plugin is skipped during discovery
- Error is logged with details
- Repository owner can be notified (optional)
- Plugin status can be checked via admin API

## Updating Plugins

When a plugin is updated:

1. Push changes to repository
2. Update `version` in `.velox.yaml`
3. Create git tag matching version
4. Wait for next scheduled scan (or trigger manual refresh)
5. Updated plugin becomes available

## Best Practices

1. **Keep description clear and concise**
2. **List all dependencies explicitly** (even transitive ones for clarity)
3. **Maintain accurate version numbers** matching git tags
4. **Link to comprehensive documentation** in README
5. **Use semantic versioning** for releases
6. **Test plugin** before tagging release
7. **Add meaningful keywords** for better discovery

## Migration from Static Registration

Existing community plugins can be migrated:

1. Create `.velox.yaml` in repository
2. Submit repository to roadrunner-plugins organization
3. Remove from static `PluginsBootloader` (optional)
4. Plugin will be auto-discovered on next scan

## Technical Implementation

### Database Schema

```sql
CREATE TABLE community_plugins
(
    id              SERIAL PRIMARY KEY,
    name            VARCHAR(100) UNIQUE NOT NULL,
    version         VARCHAR(50)         NOT NULL,
    owner           VARCHAR(100)        NOT NULL,
    repository      VARCHAR(100)        NOT NULL,
    repository_type VARCHAR(20) DEFAULT 'github',
    description     TEXT                NOT NULL,
    category        VARCHAR(50)         NOT NULL,
    dependencies    JSONB       DEFAULT '[]',
    metadata        JSONB,
    last_scanned_at TIMESTAMP,
    created_at      TIMESTAMP   DEFAULT NOW(),
    updated_at      TIMESTAMP   DEFAULT NOW()
);

CREATE INDEX idx_category ON community_plugins (category);
CREATE INDEX idx_owner ON community_plugins (owner);
```

## Notes

- Repository must be public for auto-discovery
- Private plugins can still be added manually via static registration
- YAML is cached to minimize GitHub API calls
- Rate limiting is handled automatically
- Plugins are validated before being made available

## Template .velox.yaml

Use this template to create your community plugin:

```yaml
# Required fields
name: my-plugin
version: v1.0.0
owner: roadrunner-plugins
repository: my-plugin
description: Brief description of what your plugin does (minimum 10 characters)
category: core  # Choose appropriate category
dependencies:
  - logger

# Recommended fields
docsUrl: https://github.com/roadrunner-plugins/my-plugin
author:
  name: Your Name
  email: your.email@example.com
  url: https://github.com/yourusername
keywords:
  - keyword1
  - keyword2
license: MIT

# Optional fields
# folder: /plugins/subdir
# replace: github.com/old=>github.com/new
# minRoadRunnerVersion: v2024.1.0
# goVersion: "1.21"
```
