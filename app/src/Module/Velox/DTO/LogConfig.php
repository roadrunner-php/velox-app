<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class LogConfig implements \JsonSerializable
{
    public function __construct(
        public LogLevel $level = LogLevel::Info,
        public LogMode $mode = LogMode::Production,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'level' => $this->level->value,
            'mode' => $this->mode->value,
        ];
    }
}
