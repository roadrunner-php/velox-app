<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\DTO;

enum OS: string
{
    case Linux = 'linux';
    case Darwin = 'darwin';
    case Windows = 'windows';
    case FreeBSD = 'freebsd';

    public static function current(): self
    {
        return match (\PHP_OS_FAMILY) {
            'Windows' => self::Windows,
            'Darwin' => self::Darwin,
            'BSD' => self::FreeBSD,
            default => self::Linux,
        };
    }
}
