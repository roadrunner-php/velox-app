<?php

declare(strict_types=1);

namespace App\Module\Velox\Preset\Exception;

final class PresetException extends \Exception
{
    /**
     * @param array<string> $presetNames
     */
    public function __construct(string $message, public readonly array $presetNames = [])
    {
        parent::__construct($message);
    }
}
