# Binary Builder Usage Examples

The Binary Builder feature allows you to generate TOML configuration files and build RoadRunner binaries directly from VeloxConfig objects.

## Console Commands

### Basic Binary Building

```bash
# Build with specific plugins
php app.php velox:build-binary /path/to/output --plugins=server,logger,http,jobs

# Build with default configuration (all official plugins)
php app.php velox:build-binary /path/to/output

# Build with Dockerfile
php app.php velox:build-binary /path/to/output --dockerfile --base-image=php:8.3-fpm

# Check build requirements
php app.php velox:build-binary /tmp --check-requirements
```

### Using Presets

```bash
# Build from presets
php app.php velox:generate-from-presets --presets=web-server,monitoring | php app.php velox:build-binary /path/to/output
```

## Programmatic Usage

### Basic Binary Building

```php
use App\Module\Velox\ConfigurationBuilder;
use App\Module\Velox\Configuration\DTO\VeloxConfig;

// Using the ConfigurationBuilder
$builder = $container->get(ConfigurationBuilder::class);

// Method 1: Build from VeloxConfig
$config = $builder->buildConfiguration(['server', 'logger', 'http']);
$buildResult = $builder->buildBinary($config, '/path/to/output');

if ($buildResult->isSuccess()) {
    echo "Binary built successfully!\n";
    echo "Path: {$buildResult->binaryPath}\n";
    echo "Size: {$buildResult->getBinarySize()}\n";
    echo "Build time: {$buildResult->getBuildTime()}\n";
}

// Method 2: Build from plugin selection
$buildResult = $builder->buildBinaryFromPlugins(
    ['server', 'logger', 'http', 'jobs'], 
    '/path/to/output'
);

// Method 3: Build from presets
$buildResult = $builder->buildBinaryFromPresets(
    ['web-server', 'monitoring'], 
    '/path/to/output'
);
```

### Building with Docker Support

```php
// Build binary with Dockerfile
$result = $builder->buildBinaryWithDocker($config, '/path/to/output', 'php:8.3-cli');

echo "Binary: {$result['buildResult']->binaryPath}\n";
echo "Dockerfile: {$result['dockerfilePath']}\n";
echo "Config: {$result['tomlPath']}\n";

// Then build Docker image
exec("docker build -t my-roadrunner /path/to/output");
```

### Checking Requirements

```php
$requirements = $builder->checkBuildRequirements();

if ($requirements['vx_available'] && $requirements['go_available']) {
    echo "Ready to build!\n";
} else {
    echo "Missing requirements:\n";
    if (!$requirements['vx_available']) echo "- Velox binary (vx) not found\n";
    if (!$requirements['go_available']) echo "- Go language not installed\n";
}
```

### Dependency Resolution with Building

```php
$selectedPlugins = ['http', 'jobs'];

// Resolve dependencies first
$dependencyResult = $builder->resolveDependencies($selectedPlugins);

if (!$dependencyResult->isValid) {
    echo "Dependency conflicts found:\n";
    foreach ($dependencyResult->conflicts as $conflict) {
        echo "- {$conflict->message}\n";
    }
    return;
}

// Get all required plugins
$allPlugins = array_merge(
    $selectedPlugins,
    array_map(fn($plugin) => $plugin->name, $dependencyResult->requiredPlugins)
);

// Build with resolved dependencies
$buildResult = $builder->buildBinaryFromPlugins($allPlugins, '/path/to/output');
```

### Error Handling

```php
use App\Module\Velox\BinaryBuilder\Exception\BuildException;

try {
    $buildResult = $builder->buildBinary($config, '/path/to/output');
    
    if ($buildResult->isSuccess()) {
        echo "✅ Build successful!\n";
    }
} catch (BuildException $e) {
    echo "❌ Build failed: {$e->getMessage()}\n";
    
    // Show build logs if available
    if (!empty($e->buildLogs)) {
        echo "\nBuild logs:\n";
        foreach ($e->buildLogs as $log) {
            echo "  {$log}\n";
        }
    }
}
```

### Advanced Configuration

```php
// Create custom configuration
$config = new VeloxConfig(
    roadrunner: new RoadRunnerConfig(ref: 'v2025.1.1'),
    debug: new DebugConfig(enabled: true),
    github: new GitHubConfig(
        token: new GitHubToken('your-token'),
        plugins: $githubPlugins
    ),
    log: new LogConfig(level: LogLevel::Debug, mode: LogMode::Development)
);

// Estimate build time
$estimatedTime = $builder->estimateBuildTime($config);
echo "Estimated build time: {$estimatedTime} seconds\n";

// Build the binary
$buildResult = $builder->buildBinary($config, '/path/to/output');
```

## Environment Variables

Configure the binary builder behavior through environment variables:

```bash
# Path to vx binary (if not in PATH)
VELOX_BINARY_PATH=/usr/local/bin/vx

# Build timeout in seconds
VELOX_BUILD_TIMEOUT=600

# GitHub token for private repositories
GITHUB_TOKEN=your_github_token
```

## Output Structure

When building with Docker support, the output directory will contain:

```
/path/to/output/
├── rr              # RoadRunner binary
├── Dockerfile      # Generated Dockerfile
└── velox.toml      # Generated configuration
```

## Build Requirements

The binary builder requires:

1. **Velox binary (vx)**: Install with `go install github.com/roadrunner-server/velox/v2025/cmd/vx@latest`
2. **Go language**: Version 1.22+ required
3. **Writable temp directory**: For build process
4. **GitHub token**: For private repositories (optional)

Check requirements with:
```bash
php app.php velox:build-binary /tmp --check-requirements
```
