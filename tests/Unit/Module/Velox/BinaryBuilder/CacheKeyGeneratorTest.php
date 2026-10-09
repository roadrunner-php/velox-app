<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\BinaryBuilder;

use App\Module\Velox\BinaryBuilder\DTO\TargetPlatform;
use App\Module\Velox\BinaryBuilder\Service\CacheKeyGenerator;
use App\Module\Velox\Configuration\DTO\GitHubConfig;
use App\Module\Velox\Configuration\DTO\GitHubToken;
use App\Module\Velox\Configuration\DTO\RoadRunnerConfig;
use App\Module\Velox\Configuration\DTO\VeloxConfig;
use Tests\Unit\Fixture\PluginFactory;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;

#[Test]
#[Covers(CacheKeyGenerator::class)]
final class CacheKeyGeneratorTest
{
    private CacheKeyGenerator $generator;
    private TargetPlatform $platform;

    public function __construct()
    {
        $this->generator = new CacheKeyGenerator();
        $this->platform = TargetPlatform::fromStrings('linux', 'amd64');
    }

    public function generatesKeyWithPlatformSuffix(): void
    {
        $key = $this->generator->generate($this->config(['http', 'logger']), $this->platform);

        Assert::string($key)->matchesRegex('#^rr_binary_[0-9a-f]{64}_linux/amd64$#');
    }

    public function ignoresPluginOrderAndNonBinaryFields(): void
    {
        $key = $this->generator->generate($this->config(['http', 'logger']), $this->platform);

        $reordered = $this->generator->generate($this->config(['logger', 'http']), $this->platform);
        $withToken = $this->generator->generate(
            new VeloxConfig(
                roadrunner: new RoadRunnerConfig('v2025.1.2'),
                github: new GitHubConfig(new GitHubToken('secret'), [
                    PluginFactory::plugin('http', description: 'HTTP'),
                    PluginFactory::plugin('logger'),
                ]),
            ),
            $this->platform,
        );

        Assert::same($reordered, $key);
        Assert::same($withToken, $key);
    }

    public function changesWithBinaryInputs(): void
    {
        $key = $this->generator->generate($this->config(['http']), $this->platform);

        Assert::notSame($this->generator->generate($this->config(['http', 'logger']), $this->platform), $key);
        Assert::notSame($this->generator->generate($this->config(['http'], 'v2025.1.3'), $this->platform), $key);
        Assert::notSame(
            $this->generator->generate($this->config(['http']), TargetPlatform::fromStrings('linux', 'arm64')),
            $key,
        );
    }

    /**
     * @param array<string> $pluginNames
     */
    private function config(array $pluginNames, string $rrVersion = 'v2025.1.2'): VeloxConfig
    {
        return new VeloxConfig(
            roadrunner: new RoadRunnerConfig($rrVersion),
            github: new GitHubConfig(plugins: \array_map(PluginFactory::plugin(...), $pluginNames)),
        );
    }
}
