<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\DTO;

enum Architecture: string
{
    case AMD64 = 'amd64';
    case ARM64 = 'arm64';
    case I386 = '386';
    case ARM = 'arm';

    public static function current(): self
    {
        $arch = \php_uname('m');

        return match (true) {
            \str_contains($arch, 'x86_64') || \str_contains($arch, 'amd64') => self::AMD64,
            \str_contains($arch, 'aarch64') || \str_contains($arch, 'arm64') => self::ARM64,
            \str_contains($arch, 'arm') => self::ARM,
            default => \PHP_INT_SIZE === 8 ? self::AMD64 : self::I386,
        };
    }
}
