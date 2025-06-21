<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\DTO;

enum PluginRepository: string
{
    case Github = 'github';
    case Gitlab = 'gitlab';
}
