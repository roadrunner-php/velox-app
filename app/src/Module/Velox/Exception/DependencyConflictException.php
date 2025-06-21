<?php

declare(strict_types=1);

namespace App\Module\Velox\Exception;

use Exception;

final class DependencyConflictException extends Exception
{
    /**
     * @param array<string> $conflictingPlugins
     */
    public function __construct(string $message, public readonly array $conflictingPlugins = [])
    {
        parent::__construct($message);
    }
}
