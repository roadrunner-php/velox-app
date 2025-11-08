<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryGeneration\DTO;

final readonly class PlatformInfo
{
    public function __construct(
        public PlatformType $platform,
        public string $os,
        public string $arch,
        public string $veloxBinaryUrl,
        public string $veloxBinaryName,
        public bool $isWindows = false,
    ) {}

    public static function fromPlatformString(string $platform): self
    {
        $platformEnum = PlatformType::from($platform);

        return match ($platformEnum) {
            PlatformType::LinuxAmd64 => new self(
                platform: $platformEnum,
                os: 'linux',
                arch: 'amd64',
                veloxBinaryUrl: 'https://github.com/roadrunner-server/velox/releases/latest/download/velox-linux-amd64',
                veloxBinaryName: 'vx',
            ),
            PlatformType::LinuxArm64 => new self(
                platform: $platformEnum,
                os: 'linux',
                arch: 'arm64',
                veloxBinaryUrl: 'https://github.com/roadrunner-server/velox/releases/latest/download/velox-linux-arm64',
                veloxBinaryName: 'vx',
            ),
            PlatformType::DarwinAmd64 => new self(
                platform: $platformEnum,
                os: 'darwin',
                arch: 'amd64',
                veloxBinaryUrl: 'https://github.com/roadrunner-server/velox/releases/latest/download/velox-darwin-amd64',
                veloxBinaryName: 'vx',
            ),
            PlatformType::DarwinArm64 => new self(
                platform: $platformEnum,
                os: 'darwin',
                arch: 'arm64',
                veloxBinaryUrl: 'https://github.com/roadrunner-server/velox/releases/latest/download/velox-darwin-arm64',
                veloxBinaryName: 'vx',
            ),
            PlatformType::WindowsAmd64 => new self(
                platform: $platformEnum,
                os: 'windows',
                arch: 'amd64',
                veloxBinaryUrl: 'https://github.com/roadrunner-server/velox/releases/latest/download/velox-windows-amd64.exe',
                veloxBinaryName: 'vx.exe',
                isWindows: true,
            ),
        };
    }

    public function getExecutableName(): string
    {
        return $this->isWindows ? 'rr.exe' : 'rr';
    }

    public function getShellExtension(): string
    {
        return $this->isWindows ? 'bat' : 'sh';
    }
}
