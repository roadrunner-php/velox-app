<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

enum LogMode: string
{
    case Development = 'development';
    case Production = 'production';
}
