<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Exception;

final class VeloxTimeoutException extends \RuntimeException
{
    public function __construct(int $timeoutSeconds, public readonly ?string $requestId = null, ?\Throwable $previous = null)
    {
        $message = $requestId !== null
            ? "Velox build timed out after {$timeoutSeconds}s (request_id: {$requestId})"
            : "Velox build timed out after {$timeoutSeconds}s";

        parent::__construct($message, 0, $previous);
    }
}
