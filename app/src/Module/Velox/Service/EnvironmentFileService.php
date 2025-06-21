<?php

declare(strict_types=1);

namespace App\Module\Velox\Service;

use App\Module\Velox\Exception\EnvironmentFileException;

final readonly class EnvironmentFileService
{
    public function __construct(
        private string $envFilePath = '.env',
        private bool $createBackup = true,
    ) {}

    /**
     * Read all environment variables from .env file
     *
     * @return array<string, string>
     */
    public function readEnvironmentFile(): array
    {
        if (!\file_exists($this->envFilePath)) {
            return [];
        }

        $content = \file_get_contents($this->envFilePath);
        if ($content === false) {
            throw new EnvironmentFileException("Cannot read environment file: {$this->envFilePath}");
        }

        return $this->parseEnvironmentContent($content);
    }

    /**
     * Write environment variables to .env file
     *
     * @param array<string, string> $variables
     */
    public function writeEnvironmentFile(array $variables): void
    {
        if ($this->createBackup && \file_exists($this->envFilePath)) {
            $this->createBackupFile();
        }

        $content = $this->buildEnvironmentContent($variables);

        if (\file_put_contents($this->envFilePath, $content) === false) {
            throw new EnvironmentFileException("Cannot write to environment file: {$this->envFilePath}");
        }
    }

    /**
     * Update specific environment variable
     */
    public function updateEnvironmentVariable(string $key, string $value): void
    {
        $variables = $this->readEnvironmentFile();
        $variables[$key] = $value;
        $this->writeEnvironmentFile($variables);
    }

    /**
     * Update multiple environment variables
     *
     * @param array<string, string> $updates
     */
    public function updateEnvironmentVariables(array $updates): void
    {
        $variables = $this->readEnvironmentFile();
        $variables = \array_merge($variables, $updates);
        $this->writeEnvironmentFile($variables);
    }

    /**
     * Get environment variable value
     */
    public function getEnvironmentVariable(string $key): ?string
    {
        $variables = $this->readEnvironmentFile();
        return $variables[$key] ?? null;
    }

    /**
     * Check if environment variable exists
     */
    public function hasEnvironmentVariable(string $key): bool
    {
        $variables = $this->readEnvironmentFile();
        return \array_key_exists($key, $variables);
    }

    /**
     * Get all plugin-related environment variables
     *
     * @return array<string, string>
     */
    public function getPluginEnvironmentVariables(): array
    {
        $variables = $this->readEnvironmentFile();

        return \array_filter(
            $variables,
            static fn(string $key) => \str_starts_with($key, 'RR_PLUGIN_'),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * Create backup of current .env file
     */
    public function createBackupFile(): void
    {
        if (!\file_exists($this->envFilePath)) {
            return;
        }

        $backupPath = $this->envFilePath . '.backup.' . \date('Y-m-d_H-i-s');

        if (!\copy($this->envFilePath, $backupPath)) {
            throw new EnvironmentFileException("Cannot create backup file: {$backupPath}");
        }
    }

    /**
     * Restore from the most recent backup
     */
    public function restoreFromBackup(): void
    {
        $backupFiles = \glob($this->envFilePath . '.backup.*');

        if (empty($backupFiles)) {
            throw new EnvironmentFileException('No backup files found');
        }

        // Sort by modification time (newest first)
        \usort($backupFiles, static fn(string $a, string $b) => \filemtime($b) <=> \filemtime($a));

        $latestBackup = $backupFiles[0];

        if (!\copy($latestBackup, $this->envFilePath)) {
            throw new EnvironmentFileException("Cannot restore from backup: {$latestBackup}");
        }
    }

    /**
     * Parse environment file content into key-value pairs
     *
     * @return array<string, string>
     */
    private function parseEnvironmentContent(string $content): array
    {
        $variables = [];
        $lines = \explode("\n", $content);

        foreach ($lines as $line) {
            $line = \trim($line);

            // Skip empty lines and comments
            if ($line === '' || \str_starts_with($line, '#')) {
                continue;
            }

            // Parse KEY=VALUE format
            if (\str_contains($line, '=')) {
                [$key, $value] = \explode('=', $line, 2);
                $key = \trim($key);
                $value = \trim($value);

                // Remove quotes if present
                $value = \trim($value, '"\'');

                $variables[$key] = $value;
            }
        }

        return $variables;
    }

    /**
     * Build environment file content from variables
     *
     * @param array<string, string> $variables
     */
    private function buildEnvironmentContent(array $variables): string
    {
        $lines = [];

        // Add header comment
        $lines[] = '# Environment Configuration';
        $lines[] = '# Generated on ' . \date('Y-m-d H:i:s');
        $lines[] = '';

        // Group variables by prefix for better organization
        $grouped = $this->groupVariablesByPrefix($variables);

        foreach ($grouped as $prefix => $vars) {
            if ($prefix !== '') {
                $lines[] = "# {$prefix}";
            }

            foreach ($vars as $key => $value) {
                $lines[] = $this->formatEnvironmentLine($key, $value);
            }

            $lines[] = '';
        }

        return \implode("\n", $lines);
    }

    /**
     * Group variables by their prefix for better organization
     *
     * @param array<string, string> $variables
     * @return array<string, array<string, string>>
     */
    private function groupVariablesByPrefix(array $variables): array
    {
        $grouped = [
            'RR_PLUGIN' => [],
            'GITHUB' => [],
            'GITLAB' => [],
            '' => [], // Other variables
        ];

        foreach ($variables as $key => $value) {
            $prefix = '';

            if (\str_starts_with($key, 'RR_PLUGIN_')) {
                $prefix = 'RR_PLUGIN';
            } elseif (\str_starts_with($key, 'GITHUB_')) {
                $prefix = 'GITHUB';
            } elseif (\str_starts_with($key, 'GITLAB_')) {
                $prefix = 'GITLAB';
            }

            $grouped[$prefix][$key] = $value;
        }

        // Remove empty groups
        return \array_filter($grouped, static fn(array $vars) => !empty($vars));
    }

    /**
     * Format environment variable line
     */
    private function formatEnvironmentLine(string $key, string $value): string
    {
        // Quote value if it contains spaces or special characters
        if (\preg_match('/\s|[#$]/', $value)) {
            $value = '"' . \str_replace('"', '\\"', $value) . '"';
        }

        return "{$key}={$value}";
    }
}
