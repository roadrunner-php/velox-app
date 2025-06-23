<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Exception;

final class BuildException extends \Exception
{
    /**
     * @param array<string> $buildLogs
     */
    public function __construct(string $message, public readonly array $buildLogs = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
