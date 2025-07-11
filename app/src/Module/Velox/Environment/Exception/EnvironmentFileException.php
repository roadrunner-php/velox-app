<?php

declare(strict_types=1);

namespace App\Module\Velox\Environment\Exception;

final class EnvironmentFileException extends \Exception
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
