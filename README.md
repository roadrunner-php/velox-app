<p align="center">
    <a href="https://roadrunner.dev"><picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://github.com/roadrunner-server/.github/assets/8040338/e6bde856-4ec6-4a52-bd5b-bfe78736c1ff">
        <img alt="RoadRunner" src="https://github.com/roadrunner-server/.github/assets/8040338/040fb694-1dd3-4865-9d29-8e0748c2c8b8" style="width: 6in; display: block">
    </picture></a>
</p>

<p align="center">Velox Configuration Builder — build a custom RoadRunner binary in a few clicks</p>

<div align="center">

[![Documentation](https://img.shields.io/badge/Documentation-blue?style=for-the-badge&logo=gitbook&logoColor=white)](https://docs.roadrunner.dev)
[![Sponsor](https://img.shields.io/static/v1?style=for-the-badge&label=&message=Sponsor&logo=githubsponsors&logoColor=white&color=%23EA4AAA)](https://github.com/sponsors/roadrunner-server)

</div>

<br />

A Spiral application with a SPA frontend that helps you pick RoadRunner plugins, resolves their dependencies and
generates a ready-to-use Velox configuration, Dockerfile or a prebuilt binary.

## Get Started

### Installation

The development stack runs in Docker Compose behind Traefik:

```bash
make up
```

On the first run `make` creates `.env` from `.env.example` — set `GITHUB_TOKEN` there to avoid GitHub API rate limits.
The API is then available at `http://app.vx.localhost` and the SPA at `http://spa.vx.localhost`.

### Documentation

See the [application documentation](docs/README.md) for the architecture, modules, API endpoints and development guide.

## 🧭 Purpose of the Component

Velox Configuration Builder is a comprehensive web application that simplifies the creation of custom RoadRunner server
configurations. It allows developers to visually select plugins, resolve dependencies automatically, and generate
production-ready configurations with just a few clicks, eliminating the complexity of manual TOML configuration and
binary building.

## 📝 Detailed Description for Business Analyst and Marketing

The Velox Configuration Builder solves a critical problem in the PHP application server ecosystem. RoadRunner is a
powerful application server, but configuring it with the right plugins and dependencies can be extremely complex and
time-consuming for development teams.

**Business Problem Solved:**

- **Complexity Reduction**: Manual RoadRunner configuration requires deep technical knowledge of plugin dependencies and
  version compatibility
- **Time Savings**: What used to take hours of research and testing now takes minutes with visual selection
- **Error Prevention**: Automatic dependency resolution prevents configuration conflicts that could break production
  systems
- **Standardization**: Teams can use predefined presets ensuring consistent configurations across projects

**Business Value:**

- **Faster Time-to-Market**: Developers can focus on business logic instead of infrastructure configuration
- **Reduced Support Costs**: Fewer configuration-related issues mean less debugging and support overhead
- **Improved Developer Experience**: Visual interface makes RoadRunner accessible to developers of all skill levels
- **Enterprise Readiness**: Built-in validation and best practices ensure production-ready configurations

**System Integration:**
This component serves as the central orchestrator for RoadRunner server customization, integrating with GitHub/GitLab
repositories for plugin management, Docker for containerization, and providing both web UI and CLI interfaces for
different user preferences.

## 🔄 Mermaid Sequence Diagram for Business Analyst and Marketing

### Plugin Selection and Configuration Generation Flow

```mermaid
sequenceDiagram
    participant User as Developer
    participant UI as Web Interface
    participant CB as Configuration Builder
    participant PP as Plugin Provider
    participant DR as Dependency Resolver
    participant CG as Config Generator
    participant VB as Binary Builder
    User ->> UI: Select plugins or presets
    UI ->> CB: Request configuration build
    CB ->> PP: Get available plugins
    PP -->> CB: Return plugin catalog
    CB ->> DR: Resolve dependencies
    DR -->> CB: Return dependency tree
    CB ->> CG: Generate TOML config
    CG -->> CB: Return configuration
    CB ->> VB: Build RoadRunner binary
    VB -->> CB: Return build result
    CB -->> UI: Configuration + Binary ready
    UI -->> User: Download files + Docker setup
```

### Preset-Based Configuration Flow

```mermaid
sequenceDiagram
    participant User as Developer
    participant PS as Preset Service
    participant PM as Preset Merger
    participant CB as Configuration Builder
    participant VB as Binary Builder
    User ->> PS: Select presets (web-server, monitoring)
    PS ->> PM: Merge selected presets
    PM -->> PS: Combined plugin list
    PS ->> CB: Build configuration from plugins
    CB ->> VB: Generate binary + Docker files
    VB -->> CB: Complete package
    CB -->> User: Ready-to-deploy solution
```

### Version Management and Updates Flow

```mermaid
sequenceDiagram
    participant Admin as System Admin
    participant VM as Version Manager
    participant GH as GitHub API
    participant EF as Environment File
    participant CB as Configuration Builder
    Admin ->> VM: Check for plugin updates
    VM ->> GH: Query latest versions
    GH -->> VM: Return version info
    VM ->> EF: Update environment variables
    VM -->> Admin: Update summary
    Admin ->> CB: Rebuild with new versions
    CB -->> Admin: Updated configuration
```

## 🧍 List of Actors

- **Developer**: Primary user who selects plugins and generates RoadRunner configurations
- **DevOps Engineer**: Uses generated Docker files and configurations for deployment
- **System Administrator**: Manages plugin versions and updates across the organization
- **Business Analyst**: Reviews presets and configuration templates for different use cases
- **GitHub/GitLab APIs**: External services providing plugin repositories and version information
- **Docker Registry**: Target for generated container images
- **CI/CD Pipeline**: Automated systems that consume generated configurations
- **RoadRunner Server**: The target application server that runs the generated configuration

## 📐 List of Business Rules

### Plugin Management Rules

- **Official Plugin Priority**: Official RoadRunner plugins take precedence over community plugins in conflict
  resolution
- **Version Compatibility**: Only plugins with compatible major versions can be selected together
- **Dependency Enforcement**: Required dependencies must be automatically included when a plugin is selected
- **GitHub Rate Limiting**: Anonymous GitHub access is limited to 60 requests/hour; authenticated access is required for
  production use

### Configuration Validation Rules

- **Minimum Requirements**: Every configuration must include the 'server' plugin as it's essential for RoadRunner
  operation
- **Token Validation**: GitHub/GitLab tokens are recommended when using respective platform plugins to avoid rate
  limiting
- **Master Branch Warning**: Using 'master' branch in production configurations triggers validation warnings

### Preset Management Rules

- **Preset Priority**: Higher priority presets override lower priority ones during merging
- **Conflict Detection**: Presets containing conflicting functionality groups trigger warnings
- **Completeness Validation**: All plugins referenced in presets must exist in the plugin catalog
- **Tag-Based Filtering**: Presets can be filtered by environment tags (production, development, testing)

### Binary Building Rules

- **Remote Build**: Binaries are built by a Velox server (`VELOX_SERVER_URL`), not inside the PHP worker
- **Build Timeout**: A build that takes longer than 5 minutes is aborted

| Rule Category         | Validation Type | Action              | Business Impact                                |
|-----------------------|-----------------|---------------------|------------------------------------------------|
| Plugin Dependencies   | Error           | Block configuration | Prevents broken deployments                    |
| Version Compatibility | Warning         | Allow with notice   | Maintains flexibility while highlighting risks |
| Token Requirements    | Warning         | Allow with notice   | Prevents rate limiting issues                  |
| Build Requirements    | Error           | Block build         | Ensures build environment is ready             |

## 📚 Domain Ubiquitous Terminology

- **Velox**: The RoadRunner binary builder tool that compiles custom server configurations
- **Plugin**: Modular component that adds specific functionality to RoadRunner (HTTP, Jobs, KV storage)
- **Preset**: Pre-defined combination of plugins optimized for specific use cases (web-server, queue-server)
- **Dependency Resolution**: Automatic process of including required plugins when others are selected
- **TOML Configuration**: Human-readable configuration file format used by RoadRunner
- **Binary Building**: Process of compiling a custom RoadRunner executable with selected plugins
- **Repository Type**: Platform hosting the plugin (GitHub or GitLab)
- **Plugin Source**: Classification of plugin origin (Official from RoadRunner team vs Community)
- **Environment Variables**: Configuration values stored in .env files for tokens and plugin versions
- **Docker Artifact**: Generated Dockerfile and related files for containerized deployment
- **Plugin Category**: Functional grouping (Core, HTTP, Jobs, KV, Metrics, etc.)
- **Version Ref**: Git reference (tag, branch, or commit) specifying which plugin version to use
- **Build Hash**: Unique identifier for a specific configuration build for caching purposes

## 🧪 Simple Use Cases

### Use Case 1: New Developer Creates Web Server Configuration

**Actor**: Junior PHP Developer
**Goal**: Create a simple HTTP server configuration for a new project

**Steps**:

1. Developer opens Velox Configuration Builder web interface
2. Selects "web-server" preset from the available options
3. System automatically includes: server, logger, http, headers, gzip, static, fileserver, status plugins
4. Developer reviews the generated configuration in the preview panel
5. Clicks "Generate" to create TOML configuration and Dockerfile
6. Downloads the package and deploys using Docker

**Outcome**: Working RoadRunner server ready for PHP web application in under 5 minutes

### Use Case 2: DevOps Engineer Builds Custom Microservices Setup

**Actor**: Senior DevOps Engineer
**Goal**: Create optimized configuration for microservices architecture

**Steps**:

1. Engineer selects individual plugins: server, logger, http, grpc, rpc, metrics, prometheus, otel
2. System validates dependencies and suggests additional required plugins
3. Engineer reviews dependency tree and approves automatic inclusions
4. Configures GitHub token for authenticated plugin downloads
5. Generates both TOML configuration and builds binary directly
6. System provides complete package with binary, Dockerfile, and monitoring setup

**Outcome**: Production-ready microservices server with full observability stack

### Use Case 3: System Administrator Updates Plugin Versions

**Actor**: System Administrator
**Goal**: Keep all plugin versions current and secure across the organization

**Steps**:

1. Administrator runs version check command via CLI
2. System queries GitHub APIs for latest stable plugin versions
3. Generates report showing outdated plugins and recommended updates
4. Administrator approves updates for compatible versions
5. System updates environment configuration files
6. New configurations are rebuilt automatically with updated plugin versions

**Outcome**: All RoadRunner deployments use latest secure plugin versions with minimal manual intervention
