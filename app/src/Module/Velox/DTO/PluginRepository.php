<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

enum PluginRepository: string
{
    case Github = 'github';
    case Gitlab = 'gitlab';
}
