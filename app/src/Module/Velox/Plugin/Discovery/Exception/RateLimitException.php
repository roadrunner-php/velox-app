<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Exception;

/**
 * Thrown when GitHub API rate limit is exceeded
 */
final class RateLimitException extends GitHubApiException
{
    public function __construct(
        public readonly int $resetTimestamp,
        string $repository = '',
    ) {
        $resetDate = \date('Y-m-d H:i:s', $resetTimestamp);
        parent::__construct(
            "GitHub API rate limit exceeded. Resets at: {$resetDate}",
            $repository,
            403,
        );
    }
}
