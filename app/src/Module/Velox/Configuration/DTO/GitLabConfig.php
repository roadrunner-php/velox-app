<?php

declare(strict_types=1);

namespace App\Module\Velox\Configuration\DTO;

use App\Module\Velox\Plugin\DTO\Plugin;

final readonly class GitLabConfig implements \JsonSerializable
{
    /**
     * @param array<Plugin> $plugins
     */
    public function __construct(
        public ?GitLabToken $token = null,
        public ?GitLabEndpoint $endpoint = null,
        public array $plugins = [],
    ) {}

    public function jsonSerialize(): array
    {
        $data = [];

        if ($this->token !== null) {
            $data['token'] = $this->token;
        }

        if ($this->endpoint !== null) {
            $data['endpoint'] = $this->endpoint;
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
