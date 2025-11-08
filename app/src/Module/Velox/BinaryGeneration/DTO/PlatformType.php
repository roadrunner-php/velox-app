<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryGeneration\DTO;

enum PlatformType: string
{
    case LinuxAmd64 = 'linux/amd64';
    case LinuxArm64 = 'linux/arm64';
    case DarwinAmd64 = 'darwin/amd64';
    case DarwinArm64 = 'darwin/arm64';
    case WindowsAmd64 = 'windows/amd64';
}

