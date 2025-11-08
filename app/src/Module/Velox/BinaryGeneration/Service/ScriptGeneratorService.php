<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryGeneration\Service;

use App\Module\Velox\BinaryGeneration\DTO\PlatformInfo;
use App\Module\Velox\BinaryGeneration\DTO\ScriptRequest;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use App\Module\Velox\ConfigurationBuilder;

final readonly class ScriptGeneratorService implements ScriptGeneratorInterface
{
    public function __construct(
        private ConfigurationBuilder $configBuilder,
    ) {}

    public function generateScript(ScriptRequest $request, PlatformInfo $platform): string
    {
        return match ($platform->os) {
            default => $this->generateUnixScript($request, $platform),
        };
    }

    public function getSupportedFormats(): array
    {
        return ['bash', 'powershell'];
    }

    private function generateUnixScript(ScriptRequest $request, PlatformInfo $platform): string
    {
        $config = $this->buildConfiguration($request);
        $tomlContent = $this->configBuilder->generateToml($config);

        $script = [];

        // Script header
        $script[] = '#!/bin/bash';
        $script[] = '# RoadRunner Binary Builder Script';
        $script[] = '# Generated on ' . \date('Y-m-d H:i:s');
        $script[] = '';
        $script[] = 'set -e  # Exit on any error';
        $script[] = '';

        // Color definitions
        $script[] = '# Colors for output';
        $script[] = 'RED=\'\\033[0;31m\'';
        $script[] = 'GREEN=\'\\033[0;32m\'';
        $script[] = 'YELLOW=\'\\033[1;33m\'';
        $script[] = 'BLUE=\'\\033[0;34m\'';
        $script[] = 'NC=\'\\033[0m\' # No Color';
        $script[] = '';

        // Helper functions
        $script[] = '# Helper functions';
        $script[] = 'log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }';
        $script[] = 'log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }';
        $script[] = 'log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }';
        $script[] = 'log_error() { echo -e "${RED}[ERROR]${NC} $1"; }';
        $script[] = '';

        // Platform info
        $script[] = '# Platform Information';
        $script[] = "PLATFORM=\"{$platform->platform->value}\"";
        $script[] = "OS=\"{$platform->os}\"";
        $script[] = "ARCH=\"{$platform->arch}\"";
        $script[] = "VELOX_URL=\"{$platform->veloxBinaryUrl}\"";
        $script[] = "VELOX_BINARY=\"{$platform->veloxBinaryName}\"";
        $script[] = "RR_BINARY=\"{$platform->getExecutableName()}\"";
        $script[] = '';

        // Start script
        $script[] = 'log_info "🚀 Starting RoadRunner Binary Builder"';
        $script[] = 'log_info "Platform: $PLATFORM"';
        $script[] = '';

        // Check for existing tools
        $script[] = '# Check system requirements';
        $script[] = 'check_command() {';
        $script[] = '    if command -v "$1" >/dev/null 2>&1; then';
        $script[] = '        log_success "$1 is installed"';
        $script[] = '        return 0';
        $script[] = '    else';
        $script[] = '        log_warning "$1 is not installed"';
        $script[] = '        return 1';
        $script[] = '    fi';
        $script[] = '}';
        $script[] = '';

        // Check Golang
        $script[] = '# Check Go installation';
        $script[] = 'if ! check_command go; then';
        $script[] = '    log_info "Installing Go..."';
        $script[] = '    if [[ "$OS" == "darwin" ]]; then';
        $script[] = '        if command -v brew >/dev/null 2>&1; then';
        $script[] = '            brew install go';
        $script[] = '        else';
        $script[] = '            log_error "Please install Homebrew first: https://brew.sh/"';
        $script[] = '            exit 1';
        $script[] = '        fi';
        $script[] = '    else';
        $script[] = '        # Linux installation';
        $script[] = '        GO_VERSION="1.22.5"';
        $script[] = '        GO_ARCHIVE="go${GO_VERSION}.linux-${ARCH}.tar.gz"';
        $script[] = '        wget -q "https://go.dev/dl/${GO_ARCHIVE}"';
        $script[] = '        sudo rm -rf /usr/local/go';
        $script[] = '        sudo tar -C /usr/local -xzf "${GO_ARCHIVE}"';
        $script[] = '        rm "${GO_ARCHIVE}"';
        $script[] = '        export PATH="/usr/local/go/bin:$PATH"';
        $script[] = '        echo \'export PATH="/usr/local/go/bin:$PATH"\' >> ~/.bashrc';
        $script[] = '        log_success "Go installed successfully"';
        $script[] = '    fi';
        $script[] = 'fi';
        $script[] = '';

        // Check Go version
        $script[] = '# Verify Go version';
        $script[] = 'GO_VERSION=$(go version | cut -d" " -f3 | tr -d "go")';
        $script[] = 'log_info "Go version: $GO_VERSION"';
        $script[] = '';

        // Download Velox
        $script[] = '# Download Velox binary';
        $script[] = 'log_info "Downloading Velox binary..."';
        $script[] = 'if command -v curl >/dev/null 2>&1; then';
        $script[] = '    curl -L -o "$VELOX_BINARY" "$VELOX_URL"';
        $script[] = 'elif command -v wget >/dev/null 2>&1; then';
        $script[] = '    wget -O "$VELOX_BINARY" "$VELOX_URL"';
        $script[] = 'else';
        $script[] = '    log_error "Neither curl nor wget is available"';
        $script[] = '    exit 1';
        $script[] = 'fi';
        $script[] = 'chmod +x "$VELOX_BINARY"';
        $script[] = 'log_success "Velox binary downloaded and made executable"';
        $script[] = '';

        // GitHub token handling
        $script[] = '# GitHub Token handling';
        $script[] = 'if [[ -z "${GITHUB_TOKEN:-}" ]]; then';
        if ($request->githubToken) {
            $script[] = "    export GITHUB_TOKEN=\"{$request->githubToken}\"";
            $script[] = '    log_info "Using provided GitHub token"';
        } else {
            $script[] = '    log_warning "GITHUB_TOKEN not set in environment"';
            $script[] = '    echo -n "Enter your GitHub token (or press Enter to skip): "';
            $script[] = '    read -s GITHUB_TOKEN';
            $script[] = '    echo';
            $script[] = '    if [[ -n "$GITHUB_TOKEN" ]]; then';
            $script[] = '        export GITHUB_TOKEN';
            $script[] = '        log_info "GitHub token set"';
            $script[] = '    else';
            $script[] = '        log_warning "Proceeding without GitHub token (rate limits may apply)"';
            $script[] = '    fi';
        }
        $script[] = 'else';
        $script[] = '    log_info "Using GitHub token from environment"';
        $script[] = 'fi';
        $script[] = '';

        // Create velox.toml
        $script[] = '# Create velox.toml configuration';
        $script[] = 'log_info "Creating velox.toml configuration..."';
        $script[] = 'cat > velox.toml << \'EOF\'';
        $script[] = $tomlContent;
        $script[] = 'EOF';
        $script[] = 'log_success "Configuration file created"';
        $script[] = '';

        // Build RoadRunner
        $script[] = '# Build RoadRunner binary';
        $script[] = 'log_info "Building RoadRunner binary..."';
        $script[] = './"$VELOX_BINARY" build -c velox.toml -o .';
        $script[] = '';

        // Verify build
        $script[] = '# Verify build';
        $script[] = 'if [[ -f "$RR_BINARY" ]]; then';
        $script[] = '    RR_SIZE=$(stat -f%z "$RR_BINARY" 2>/dev/null || stat -c%s "$RR_BINARY" 2>/dev/null || echo "unknown")';
        $script[] = '    log_success "✅ RoadRunner binary built successfully!"';
        $script[] = '    log_info "📁 Binary location: ./$RR_BINARY"';
        $script[] = '    log_info "📦 Binary size: $RR_SIZE bytes"';
        $script[] = '    echo';
        $script[] = '    log_info "🔧 To test your binary:"';
        $script[] = '    echo "  ./$RR_BINARY --version"';
        $script[] = '    echo "  ./$RR_BINARY serve"';
        $script[] = 'else';
        $script[] = '    log_error "❌ Build failed - binary not found"';
        $script[] = '    exit 1';
        $script[] = 'fi';
        $script[] = '';

        // Cleanup
        $script[] = '# Cleanup';
        $script[] = 'log_info "Cleaning up temporary files..."';
        $script[] = 'rm -f "$VELOX_BINARY" velox.toml';
        $script[] = 'log_success "🎉 Build process completed successfully!"';

        return \implode("\n", $script);
    }

    private function generateWindowsScript(ScriptRequest $request, PlatformInfo $platform): string
    {
        $config = $this->buildConfiguration($request);
        $tomlContent = $this->configBuilder->generateToml($config);

        $script = [];

        // Script header
        $script[] = '@echo off';
        $script[] = 'REM RoadRunner Binary Builder Script for Windows';
        $script[] = 'REM Generated on ' . \date('Y-m-d H:i:s');
        $script[] = '';
        $script[] = 'setlocal enabledelayedexpansion';
        $script[] = '';

        // Platform info
        $script[] = 'REM Platform Information';
        $script[] = "set PLATFORM={$platform->platform->value}";
        $script[] = "set OS={$platform->os}";
        $script[] = "set ARCH={$platform->arch}";
        $script[] = "set VELOX_URL={$platform->veloxBinaryUrl}";
        $script[] = "set VELOX_BINARY={$platform->veloxBinaryName}";
        $script[] = "set RR_BINARY={$platform->getExecutableName()}";
        $script[] = '';

        // Start script
        $script[] = 'echo ^🚀 Starting RoadRunner Binary Builder';
        $script[] = 'echo Platform: %PLATFORM%';
        $script[] = 'echo.';
        $script[] = '';

        // Check PowerShell
        $script[] = 'REM Check if PowerShell is available';
        $script[] = 'powershell -Command "Write-Host \'PowerShell is available\'" >nul 2>&1';
        $script[] = 'if errorlevel 1 (';
        $script[] = '    echo ERROR: PowerShell is required but not found';
        $script[] = '    exit /b 1';
        $script[] = ')';
        $script[] = '';

        // Download Velox using PowerShell
        $script[] = 'REM Download Velox binary';
        $script[] = 'echo Downloading Velox binary...';
        $script[] = 'powershell -Command "Invoke-WebRequest -Uri \'%VELOX_URL%\' -OutFile \'%VELOX_BINARY%\'"';
        $script[] = 'if errorlevel 1 (';
        $script[] = '    echo ERROR: Failed to download Velox binary';
        $script[] = '    exit /b 1';
        $script[] = ')';
        $script[] = 'echo ✅ Velox binary downloaded';
        $script[] = '';

        // GitHub token handling
        $script[] = 'REM GitHub Token handling';
        $script[] = 'if "%GITHUB_TOKEN%"=="" (';
        if ($request->githubToken) {
            $script[] = "    set GITHUB_TOKEN={$request->githubToken}";
            $script[] = '    echo Using provided GitHub token';
        } else {
            $script[] = '    echo WARNING: GITHUB_TOKEN not set in environment';
            $script[] = '    set /p GITHUB_TOKEN="Enter your GitHub token (or press Enter to skip): "';
            $script[] = '    if not "!GITHUB_TOKEN!"=="" (';
            $script[] = '        echo GitHub token set';
            $script[] = '    ) else (';
            $script[] = '        echo WARNING: Proceeding without GitHub token (rate limits may apply)';
            $script[] = '    )';
        }
        $script[] = ') else (';
        $script[] = '    echo Using GitHub token from environment';
        $script[] = ')';
        $script[] = '';

        // Create velox.toml using PowerShell
        $script[] = 'REM Create velox.toml configuration';
        $script[] = 'echo Creating velox.toml configuration...';
        $script[] = '(';
        foreach (\explode("\n", $tomlContent) as $line) {
            $escapedLine = \str_replace('"', '""', $line);
            $script[] = "echo {$escapedLine}";
        }
        $script[] = ') > velox.toml';
        $script[] = 'echo ✅ Configuration file created';
        $script[] = '';

        // Build RoadRunner
        $script[] = 'REM Build RoadRunner binary';
        $script[] = 'echo Building RoadRunner binary...';
        $script[] = '%VELOX_BINARY% build -c velox.toml -o .';
        $script[] = 'if errorlevel 1 (';
        $script[] = '    echo ERROR: Build failed';
        $script[] = '    exit /b 1';
        $script[] = ')';
        $script[] = '';

        // Verify build
        $script[] = 'REM Verify build';
        $script[] = 'if exist "%RR_BINARY%" (';
        $script[] = '    echo ✅ RoadRunner binary built successfully!';
        $script[] = '    echo 📁 Binary location: .\\%RR_BINARY%';
        $script[] = '    echo.';
        $script[] = '    echo 🔧 To test your binary:';
        $script[] = '    echo   .\\%RR_BINARY% --version';
        $script[] = '    echo   .\\%RR_BINARY% serve';
        $script[] = ') else (';
        $script[] = '    echo ERROR: Build failed - binary not found';
        $script[] = '    exit /b 1';
        $script[] = ')';
        $script[] = '';

        // Cleanup
        $script[] = 'REM Cleanup';
        $script[] = 'echo Cleaning up temporary files...';
        $script[] = 'del "%VELOX_BINARY%" velox.toml 2>nul';
        $script[] = 'echo 🎉 Build process completed successfully!';
        $script[] = 'pause';

        return \implode("\r\n", $script);
    }

    private function buildConfiguration(ScriptRequest $request): VeloxConfig
    {
        if ($request->hasPresets()) {
            return $this->configBuilder->buildConfigurationFromPresets(
                $request->presets,
                $request->githubToken,
            );
        }

        return $this->configBuilder->buildConfiguration(
            $request->plugins,
            $request->githubToken,
        );
    }
}
