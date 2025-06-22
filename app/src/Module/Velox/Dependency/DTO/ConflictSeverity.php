<?php

declare(strict_types=1);

namespace App\Module\Velox\Dependency\DTO;

enum ConflictSeverity: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Info = 'info';
}
