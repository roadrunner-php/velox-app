<?php

declare(strict_types=1);

namespace App\Module\Velox\DTO;

final readonly class GitHubConfig implements \JsonSerializable
{
    /**
     * @param array<Plugin> $plugins
     */
    public function __construct(
        public ?GitHubToken $token = null,
        public array $plugins = [],
    ) {}

    public function jsonSerialize(): array
    {
        $data = [];

        if ($this->token !== null) {
            $data['token'] = $this->token;
        }

        if (!empty($this->plugins)) {
            $data['plugins'] = [];
            foreach ($this->plugins as $plugin) {
                $data['plugins'][$plugin->name] = $plugin;
            }
        }

        return $data;
    }
}
