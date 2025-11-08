<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryGeneration\DTO;

final readonly class ScriptRequest
{
    /**
     * @param array<string> $presets
     * @param array<string> $plugins
     */
    public function __construct(
        public array $presets = [],
        public array $plugins = [],
        public string $platform = 'auto',
        public ?string $githubToken = null,
    ) {}

    public function hasPresets(): bool
    {
        return !empty($this->presets);
    }

    public function hasPlugins(): bool
    {
        return !empty($this->plugins);
    }

    public function getAllSelections(): array
    {
        return array_unique([...$this->presets, ...$this->plugins]);
    }

    public function getConfigHash(): string
    {
        $data = [
            'presets' => $this->presets,
            'plugins' => $this->plugins,
            'platform' => $this->platform,
        ];

        return hash('sha256', json_encode($data));
    }
}
