<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Exception;

final class VeloxBuildFailedException extends \RuntimeException
{
    public function __construct(string $message, public readonly ?string $requestId = null, ?\Throwable $previous = null)
    {
        $fullMessage = $requestId !== null
            ? "Velox build failed (request_id: {$requestId}): {$message}"
            : "Velox build failed: {$message}";

        parent::__construct($fullMessage, 0, $previous);
    }
}
