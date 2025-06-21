<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

enum LogLevel: string
{
    case Debug = 'debug';
    case Info = 'info';
    case Warn = 'warn';
    case Error = 'error';
}
