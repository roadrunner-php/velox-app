<?php

declare(strict_types=1);

namespace App\Application\Storage;

use Psr\Http\Message\UriInterface;
use Spiral\Distribution\Resolver\ExpirationAwareResolver;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;
use Nyholm\Psr7\Uri;

final class RRSignedResolver extends ExpirationAwareResolver
{
    public function __construct(
        private readonly RPCInterface $rpc,
        private readonly string $bucket = 'default',
    ) {
        parent::__construct();
    }

    public function resolve(string $file, mixed $expiration = null): UriInterface
    {
        try {
            // Calculate seconds until expiration (difference between now and expiration time)
            $now = new \DateTimeImmutable();
            $expiresAt = $this->getExpirationDateTime($expiration);

            $response = $this->rpc->call('s3.GetPublicURL', [
                'bucket' => $this->bucket,
                'pathname' => $file,
                'expires_in' => $expiresAt->getTimestamp() - $now->getTimestamp(),
            ]);

            if (!isset($response['url'])) {
                throw new \RuntimeException(
                    "Invalid response from S3 plugin: missing 'url' field",
                );
            }

            return new Uri($response['url']);
        } catch (ServiceException $e) {
            throw new \RuntimeException(
                "Failed to generate signed URL for '{$file}' in bucket '{$this->bucket}': {$e->getMessage()}",
                0,
                $e,
            );
        }
    }
}
