<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

enum PluginSource: string
{
    case Official = 'official';
    case Community = 'community';
}
