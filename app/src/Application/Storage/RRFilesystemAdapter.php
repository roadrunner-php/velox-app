<?php

declare(strict_types=1);

namespace App\Application\Storage;

use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use League\Flysystem\Visibility;
use Spiral\Goridge\RPC\Exception\ServiceException;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\Storage\Storage;

/**
 * Flysystem adapter for RoadRunner S3 Plugin
 *
 * Provides seamless integration between League Flysystem and RoadRunner S3 plugin
 */
final readonly class RRFilesystemAdapter implements FilesystemAdapter, PublicUrlGenerator, TemporaryUrlGenerator
{
    public function __construct(
        private RPCInterface $rpc,
        private string $bucket = Storage::DEFAULT_STORAGE,
    ) {}

    public function fileExists(string $path): bool
    {
        try {
            $response = $this->rpc->call('s3.Exists', [
                'bucket' => $this->bucket,
                'pathname' => $path,
            ]);

            return $response['exists'] ?? false;
        } catch (ServiceException $e) {
            throw UnableToCheckExistence::forLocation($path, $e);
        }
    }

    public function directoryExists(string $path): bool
    {
        // S3 doesn't have real directories, they're just key prefixes
        // We'll consider a directory exists if we can list objects with that prefix
        // For now, return true as S3 directories are virtual
        return true;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        try {
            $request = [
                'bucket' => $this->bucket,
                'pathname' => $path,
                'content' => \base64_encode($contents),
            ];

            // Handle visibility
            if ($visibility = $config->get(Config::OPTION_VISIBILITY)) {
                $request['visibility'] = $this->mapVisibility($visibility);
            }

            // Handle metadata
            if ($metadata = $config->get('metadata')) {
                $request['config'] = $metadata;
            }

            $this->rpc->call('s3.Write', $request);
        } catch (ServiceException $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        // Read stream content
        $streamContents = \stream_get_contents($contents);

        if ($streamContents === false) {
            throw UnableToWriteFile::atLocation($path, 'Failed to read from stream');
        }

        $this->write($path, $streamContents, $config);
    }

    public function read(string $path): string
    {
        try {
            $response = $this->rpc->call('s3.Read', [
                'bucket' => $this->bucket,
                'pathname' => $path,
            ]);

            return \base64_decode((string) $response['content']);
        } catch (ServiceException $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage(), $e);
        }
    }

    public function readStream(string $path)
    {
        // Read the file content first
        $content = $this->read($path);

        // Create a stream resource from the content
        $stream = \fopen('php://temp', 'r+');

        if ($stream === false) {
            throw UnableToReadFile::fromLocation($path, 'Failed to create stream');
        }

        \fwrite($stream, $content);
        \rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        try {
            $this->rpc->call('s3.Delete', [
                'bucket' => $this->bucket,
                'pathname' => $path,
            ]);
        } catch (ServiceException $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function deleteDirectory(string $path): void
    {
        // S3 doesn't have real directories
        // To delete a directory, we would need to list all objects with the prefix
        // and delete them one by one. This requires a ListObjects operation.
        // For now, throw an exception as this operation is not yet implemented
        throw UnableToDeleteDirectory::atLocation(
            $path,
            'Directory deletion requires ListObjects operation which is not yet implemented',
        );
    }

    public function createDirectory(string $path, Config $config): void
    {
        // S3 doesn't require directory creation
        // Directories are created implicitly when files are uploaded
        // No-op operation
    }

    public function setVisibility(string $path, string $visibility): void
    {
        try {
            $this->rpc->call('s3.SetVisibility', [
                'bucket' => $this->bucket,
                'pathname' => $path,
                'visibility' => $this->mapVisibility($visibility),
            ]);
        } catch (ServiceException $e) {
            throw UnableToSetVisibility::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function visibility(string $path): FileAttributes
    {
        $metadata = $this->getMetadata($path);

        return new FileAttributes(
            $path,
            null,
            $this->reverseMapVisibility($metadata['visibility'] ?? 'private'),
        );
    }

    public function mimeType(string $path): FileAttributes
    {
        $metadata = $this->getMetadata($path);

        return new FileAttributes(
            $path,
            null,
            null,
            null,
            $metadata['mime_type'] ?? null,
        );
    }

    public function lastModified(string $path): FileAttributes
    {
        $metadata = $this->getMetadata($path);

        return new FileAttributes(
            $path,
            null,
            null,
            $metadata['last_modified'] ?? null,
        );
    }

    public function fileSize(string $path): FileAttributes
    {
        $metadata = $this->getMetadata($path);

        return new FileAttributes(
            $path,
            $metadata['size'] ?? null,
        );
    }

    public function listContents(string $path, bool $deep): iterable
    {
        try {
            $prefix = $this->normalizePath($path);

            // Prepare initial request
            $request = [
                'bucket' => $this->bucket,
                'prefix' => $prefix,
                'max_keys' => 1000,
            ];

            // For shallow listing, use delimiter to group by "directories"
            if (!$deep) {
                $request['delimiter'] = '/';
            }

            do {
                $response = $this->rpc->call('s3.ListObjects', $request);

                // Yield files
                foreach ($response['objects'] ?? [] as $object) {
                    yield $this->mapObjectToStorageAttributes($object);
                }

                // Yield directories (only for shallow listings)
                if (!$deep && isset($response['common_prefixes'])) {
                    foreach ($response['common_prefixes'] as $commonPrefix) {
                        yield $this->mapPrefixToStorageAttributes($commonPrefix);
                    }
                }

                // Handle pagination
                if ($response['is_truncated'] ?? false) {
                    $request['continuation_token'] = $response['next_continuation_token'];
                } else {
                    break;
                }
            } while (true);

        } catch (ServiceException $e) {
            throw UnableToListContents::atLocation($path, $deep, $e);
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $request = [
                'source_bucket' => $this->bucket,
                'source_pathname' => $source,
                'dest_bucket' => $this->bucket,
                'dest_pathname' => $destination,
            ];

            // Handle visibility
            if ($visibility = $config->get(Config::OPTION_VISIBILITY)) {
                $request['visibility'] = $this->mapVisibility($visibility);
            }

            $this->rpc->call('s3.Move', $request);
        } catch (ServiceException $e) {
            throw UnableToMoveFile::fromLocationTo($source, $destination, $e);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $request = [
                'source_bucket' => $this->bucket,
                'source_pathname' => $source,
                'dest_bucket' => $this->bucket,
                'dest_pathname' => $destination,
            ];

            // Handle visibility
            if ($visibility = $config->get(Config::OPTION_VISIBILITY)) {
                $request['visibility'] = $this->mapVisibility($visibility);
            }

            $this->rpc->call('s3.Copy', $request);
        } catch (ServiceException $e) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $e);
        }
    }

    public function publicUrl(string $path, Config $config): string
    {
        try {
            $response = $this->rpc->call('s3.GetPublicURL', [
                'bucket' => $this->bucket,
                'pathname' => $path,
                'expires_in' => 0, // Permanent URL
            ]);

            return $response['url'];
        } catch (ServiceException $e) {
            throw new \RuntimeException(
                "Unable to generate public URL for '{$path}': {$e->getMessage()}",
                0,
                $e,
            );
        }
    }

    public function temporaryUrl(string $path, \DateTimeInterface $expiresAt, Config $config): string
    {
        try {
            // Calculate seconds until expiration
            $now = new \DateTimeImmutable();
            $expiresIn = $expiresAt->getTimestamp() - $now->getTimestamp();

            // Ensure positive expiration time
            if ($expiresIn <= 0) {
                throw new \InvalidArgumentException(
                    'Expiration time must be in the future',
                );
            }

            $response = $this->rpc->call('s3.GetPublicURL', [
                'bucket' => $this->bucket,
                'pathname' => $path,
                'expires_in' => $expiresIn,
            ]);

            return $response['url'];
        } catch (ServiceException $e) {
            throw new \RuntimeException(
                "Unable to generate temporary URL for '{$path}': {$e->getMessage()}",
                0,
                $e,
            );
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
    }

    /**
     * Normalize path to ensure it has trailing slash if not empty
     */
    private function normalizePath(string $path): string
    {
        $path = \trim($path, '/');

        if ($path === '') {
            return '';
        }

        return $path . '/';
    }

    /**
     * Map S3 object to FileAttributes or DirectoryAttributes
     */
    private function mapObjectToStorageAttributes(array $object): StorageAttributes
    {
        $path = $object['key'];
        $lastModified = $object['last_modified'] ?? null;

        // Check if this is a directory marker (ends with /)
        if (\str_ends_with((string) $path, '/')) {
            return new DirectoryAttributes(
                \rtrim((string) $path, '/'),
                null, // visibility
                $lastModified,
            );
        }

        return new FileAttributes(
            $path,
            $object['size'] ?? null,
            null, // visibility
            $lastModified,
            $object['mime_type'] ?? null,
            [
                'etag' => $object['etag'] ?? null,
                'storage_class' => $object['storage_class'] ?? null,
            ],
        );
    }

    /**
     * Map common prefix to DirectoryAttributes
     */
    private function mapPrefixToStorageAttributes(array $prefix): DirectoryAttributes
    {
        $path = \rtrim((string) $prefix['prefix'], '/');

        return new DirectoryAttributes($path);
    }

    /**
     * Get file metadata from S3
     *
     * @throws UnableToRetrieveMetadata
     */
    private function getMetadata(string $path): array
    {
        try {
            return $this->rpc->call('s3.GetMetadata', [
                'bucket' => $this->bucket,
                'pathname' => $path,
            ]);
        } catch (ServiceException $e) {
            throw UnableToRetrieveMetadata::create(
                $path,
                'metadata',
                $e->getMessage(),
                $e,
            );
        }
    }

    /**
     * Map Flysystem visibility to S3 visibility
     *
     */
    private function mapVisibility(string $visibility): string
    {
        return match ($visibility) {
            Visibility::PUBLIC => 'public',
            Visibility::PRIVATE => 'private',
            default => 'private',
        };
    }

    /**
     * Map S3 visibility to Flysystem visibility
     *
     */
    private function reverseMapVisibility(string $visibility): string
    {
        return match ($visibility) {
            'public', 'public-read' => Visibility::PUBLIC,
            'private' => Visibility::PRIVATE,
            default => Visibility::PRIVATE,
        };
    }
}
