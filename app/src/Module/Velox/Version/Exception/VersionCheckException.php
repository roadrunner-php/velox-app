<?php

declare(strict_types=1);

namespace App\Module\Velox\Version\Exception;

use Exception;

final class VersionCheckException extends Exception
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
