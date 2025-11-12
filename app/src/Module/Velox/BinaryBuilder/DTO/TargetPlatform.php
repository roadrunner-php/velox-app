<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\DTO;

final readonly class TargetPlatform
{
    public function __construct(
        public OS $os,
        public Architecture $arch,
    ) {}

    public static function current(): self
    {
        return new self(
            os: OS::current(),
            arch: Architecture::current(),
        );
    }

    public static function fromStrings(string $os, string $arch): self
    {
        return new self(
            os: OS::from($os),
            arch: Architecture::from($arch),
        );
    }

    public function toArray(): array
    {
        return [
            'os' => $this->os->value,
            'arch' => $this->arch->value,
        ];
    }

    public function toString(): string
    {
        return "{$this->os->value}/{$this->arch->value}";
    }
}
