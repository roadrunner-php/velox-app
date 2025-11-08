<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Exception;

/**
 * Thrown when GitHub API calls fail
 */
class GitHubApiException extends DiscoveryException
{
    public function __construct(
        string $message,
        public readonly string $repository = '',
        public readonly int $statusCode = 0,
    ) {
        parent::__construct($message);
    }
}
