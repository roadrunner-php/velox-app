<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryGeneration\Service;

use Psr\Http\Message\ServerRequestInterface;
use App\Module\Velox\BinaryGeneration\DTO\PlatformInfo;
use App\Module\Velox\BinaryGeneration\DTO\PlatformType;

final readonly class PlatformDetectionService
{
    /**
     * Detect platform from request headers or explicit platform parameter
     */
    public function detectPlatform(ServerRequestInterface $request, string $platformParam = 'auto'): PlatformInfo
    {
        if ($platformParam !== 'auto') {
            return PlatformInfo::fromPlatformString($platformParam);
        }

        return $this->detectFromUserAgent($request);
    }

    /**
     * Get all supported platforms
     *
     * @return array<string, PlatformInfo>
     */
    public function getSupportedPlatforms(): array
    {
        $platforms = [];

        foreach (PlatformType::cases() as $platform) {
            $platforms[$platform->value] = PlatformInfo::fromPlatformString($platform->value);
        }

        return $platforms;
    }

    /**
     * Check if platform is supported
     */
    public function isPlatformSupported(string $platform): bool
    {
        return \array_key_exists($platform, $this->getSupportedPlatforms());
    }

    private function detectFromUserAgent(ServerRequestInterface $request): PlatformInfo
    {
        $userAgent = $request->getHeaderLine('User-Agent');

        // Default to Linux AMD64 if cannot detect
        $defaultPlatform = PlatformType::LinuxAmd64;

        if (empty($userAgent)) {
            return PlatformInfo::fromPlatformString($defaultPlatform->value);
        }

        $userAgent = \strtolower($userAgent);

        // Detect OS
        $os = 'linux'; // default
        if (\str_contains($userAgent, 'darwin') || \str_contains($userAgent, 'mac')) {
            $os = 'darwin';
        } elseif (\str_contains($userAgent, 'windows') || \str_contains($userAgent, 'win')) {
            $os = 'windows';
        }

        // Detect architecture
        $arch = 'amd64'; // default
        if (\str_contains($userAgent, 'arm64') || \str_contains($userAgent, 'aarch64')) {
            $arch = 'arm64';
        }

        $platformString = "{$os}/{$arch}";

        // Validate detected platform
        if (!$this->isPlatformSupported($platformString)) {
            return PlatformInfo::fromPlatformString($defaultPlatform->value);
        }

        return PlatformInfo::fromPlatformString($platformString);
    }
}
