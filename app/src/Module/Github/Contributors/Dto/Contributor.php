<?php

declare(strict_types=1);

namespace App\Module\Github\Contributors\Dto;

final readonly class Contributor
{
    public function __construct(
        public string $login,
        public string $avatarUrl,
        public ?int $contributionsCount = null,
        public ?int $id = null,
        public string $type = 'User',
        public bool $siteAdmin = false,
    ) {}

    public static function fromGithubApiResponse(array $data): self
    {
        return new self(
            login: $data['login'],
            avatarUrl: $data['avatar_url'],
            contributionsCount: $data['contributions'] ?? null,
            id: $data['id'] ?? null,
            type: $data['type'] ?? 'User',
            siteAdmin: $data['site_admin'] ?? false,
        );
    }
}
