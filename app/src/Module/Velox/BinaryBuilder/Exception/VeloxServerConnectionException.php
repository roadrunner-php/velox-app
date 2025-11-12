<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Exception;

final class VeloxServerConnectionException extends \RuntimeException
{
    public function __construct(string $serverUrl, ?\Throwable $previous = null)
    {
        parent::__construct("Cannot connect to Velox server at: {$serverUrl}", 0, $previous);
    }
}
