<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\DTO;

final readonly class BuildResult
{
    /**
     * @param array<string> $logs
     * @param array<string> $errors
     */
    public function __construct(
        public bool $success,
        public string $binaryPath,
        public float $buildTimeSeconds,
        public int $binarySizeBytes,
        public array $logs = [],
        public array $errors = [],
        public ?string $configPath = null,
        public ?string $buildHash = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getBinarySize(): string
    {
        return $this->formatBytes($this->binarySizeBytes);
    }

    public function getBuildTime(): string
    {
        return \number_format($this->buildTimeSeconds, 2) . 's';
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = \max($bytes, 0);
        $pow = \floor(($bytes ? \log($bytes) : 0) / \log(1024));
        $pow = \min($pow, \count($units) - 1);

        $bytes /= 1024 ** $pow;

        return \round($bytes, 2) . ' ' . $units[$pow];
    }
}
