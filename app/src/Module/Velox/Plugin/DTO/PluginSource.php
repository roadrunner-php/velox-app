<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\DTO;

enum PluginSource: string
{
    case Official = 'official';
    case Community = 'community';
}
