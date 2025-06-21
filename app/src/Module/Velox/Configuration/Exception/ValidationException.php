<?php

declare(strict_types=1);

namespace App\Module\Velox\Configuration\Exception;

use Exception;

final class ValidationException extends Exception
{
    /**
     * @param array<string> $validationErrors
     */
    public function __construct(string $message, public readonly array $validationErrors = [])
    {
        parent::__construct($message);
    }
}
