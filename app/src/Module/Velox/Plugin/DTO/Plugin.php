<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\DTO;

final readonly class Plugin implements \JsonSerializable
{
    /**
     * @param array<string> $dependencies
     */
    public function __construct(
        public string $name,
        public string $ref,
        public string $owner,
        public string $repository,
        public PluginRepository $repositoryType,
        public PluginSource $source,
        public ?string $folder = null,
        public ?string $replace = null,
        public array $dependencies = [],
        public string $description = '',
        public ?PluginCategory $category = null,
        public ?string $docsUrl = null,
    ) {}

    public function jsonSerialize(): array
    {
        $data = [
            'ref' => $this->ref,
            'owner' => $this->owner,
            'repository' => $this->repository,
        ];

        if ($this->folder !== null) {
            $data['folder'] = $this->folder;
        }

        if ($this->replace !== null) {
            $data['replace'] = $this->replace;
        }

        if ($this->docsUrl !== null) {
            $data['docs_url'] = $this->docsUrl;
        }

        return $data;
    }
}
