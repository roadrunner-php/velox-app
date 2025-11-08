<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryGeneration\Service;

use App\Module\Velox\BinaryGeneration\DTO\PlatformInfo;
use App\Module\Velox\BinaryGeneration\DTO\ScriptRequest;

interface ScriptGeneratorInterface
{
    public function generateScript(ScriptRequest $request, PlatformInfo $platform): string;

    public function getSupportedFormats(): array;
}
