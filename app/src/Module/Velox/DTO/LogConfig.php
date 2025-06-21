<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class LogConfig
{
    public function __construct(
        public LogLevel $level = LogLevel::Info,
        public LogMode $mode = LogMode::Production,
    ) {}
}
