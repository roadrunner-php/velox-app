<?php

declare(strict_types=1);

namespace App\Application\HTTP\Setter;

final class Cleanup
{
    public static function trim(mixed $value): mixed
    {
        if (\is_string($value)) {
            return \trim($value);
        }

        return $value;
    }

    public static function toString(mixed $value): string
    {
        if (\is_array($value)) {
            return '';
        }

        if (\is_string($value)) {
            return $value;
        }

        return (string) $value;
    }

    public static function toInt(mixed $value): int
    {
        return (int) $value;
    }

    public static function toNullableString(mixed $value): ?string
    {
        $string = self::toString($value);

        if (empty($value)) {
            return null;
        }

        return $string;
    }

    public static function toBoolean(mixed $value): bool
    {
        return (bool) $value;
    }
}
