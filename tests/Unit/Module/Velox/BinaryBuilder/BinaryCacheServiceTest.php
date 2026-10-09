<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\BinaryBuilder;

use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\BinaryBuilder\Service\BinaryCacheService;
use App\Module\Velox\BinaryBuilder\Service\CacheKeyGenerator;
use App\Module\Velox\Configuration\DTO\GitHubConfig;
use App\Module\Velox\Configuration\DTO\RoadRunnerConfig;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use Psr\Log\NullLogger;
use Spiral\Files\Files;
use Tests\Unit\Fixture\PluginFactory;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
#[Covers(BinaryCacheService::class)]
final class BinaryCacheServiceTest
{
    private Files $files;
    private string $directory;
    private VeloxConfig $config;
    private TargetPlatform $platform;

    public function __construct()
    {
        $this->files = new Files();
        $this->config = new VeloxConfig(
            roadrunner: new RoadRunnerConfig('v2025.1.2'),
            github: new GitHubConfig(plugins: [PluginFactory::plugin('http')]),
        );
        $this->platform = TargetPlatform::fromStrings('linux', 'amd64');
    }

    #[BeforeTest]
    public function createDirectory(): void
    {
        $this->directory = \sys_get_temp_dir() . '/velox-cache-test-' . \bin2hex(\random_bytes(6));
        $this->files->ensureDirectory($this->directory);
    }

    #[AfterTest]
    public function removeDirectory(): void
    {
        $this->files->deleteDirectory($this->directory);
    }

    public function storesAndRetrievesBinary(): void
    {
        $cache = $this->cache();
        $key = $cache->generateKey($this->config, $this->platform);
        $binary = $this->binary('binary-content');

        Assert::false($cache->has($key));

        $cache->put($key, $binary, $this->config, $this->platform);

        Assert::true($cache->has($key));

        $result = $cache->get($key, $this->directory . '/rr-out');

        Assert::notNull($result);
        Assert::true($result->fromCache);
        Assert::same($result->cacheKey, $key);
        Assert::same($result->binarySizeBytes, 14);
        Assert::same($this->files->read($this->directory . '/rr-out'), 'binary-content');
    }

    public function deletesEntry(): void
    {
        $cache = $this->cache();
        $cache->put('key', $this->binary('binary-content'), $this->config, $this->platform);

        $cache->delete('key');

        Assert::false($cache->has('key'));
        Assert::null($cache->get('key', $this->directory . '/rr-out'));
    }

    public function skipsEmptyBinary(): void
    {
        $cache = $this->cache();

        $cache->put('key', $this->binary(''), $this->config, $this->platform);

        Assert::false($cache->has('key'));
    }

    public function treatsExpiredEntryAsMissing(): void
    {
        $cache = $this->cache(ttl: 60);
        $cache->put('key', $this->binary('binary-content'), $this->config, $this->platform);
        $metadataPath = $this->directory . '/cache/key/metadata.json';
        $metadata = \json_decode($this->files->read($metadataPath), true);
        $metadata['cached_at'] = (new \DateTimeImmutable('-2 minutes'))->format(\DATE_ATOM);
        $this->files->write($metadataPath, \json_encode($metadata));

        Assert::false($cache->has('key'));
        Assert::true($this->cache(ttl: 0)->has('key'));
    }

    public function rejectsBinaryWithUnexpectedSize(): void
    {
        $cache = $this->cache();
        $cache->put('key', $this->binary('binary-content'), $this->config, $this->platform);
        $this->files->write($this->directory . '/cache/key/rr', 'tampered');

        Assert::null($cache->get('key', $this->directory . '/rr-out'));
    }

    public function treatsBrokenMetadataAsMissing(): void
    {
        $cache = $this->cache();
        $cache->put('key', $this->binary('binary-content'), $this->config, $this->platform);
        $this->files->write($this->directory . '/cache/key/metadata.json', 'not json');

        Assert::false($cache->has('key'));
        Assert::null($cache->get('key', $this->directory . '/rr-out'));
    }

    private function cache(int $ttl = 2592000): BinaryCacheService
    {
        return new BinaryCacheService(
            $this->files,
            $this->directory . '/cache',
            new CacheKeyGenerator(),
            new NullLogger(),
            $ttl,
        );
    }

    private function binary(string $content): string
    {
        $path = $this->directory . '/source-' . \bin2hex(\random_bytes(4));
        $this->files->write($path, $content);

        return $path;
    }
}
