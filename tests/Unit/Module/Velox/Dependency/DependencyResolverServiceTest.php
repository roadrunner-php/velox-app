<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Dependency;

use App\Module\Velox\Dependency\DTO\ConflictInfo;
use App\Module\Velox\Dependency\DTO\ConflictSeverity;
use App\Module\Velox\Dependency\DTO\ConflictType;
use App\Module\Velox\Dependency\Service\DependencyResolverService;
use App\Module\Velox\Plugin\DTO\Plugin;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use App\Module\Velox\Plugin\Service\ConfigPluginProvider;
use Tests\Unit\Fixture\PluginFactory;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Skip;
use Testo\Test;

#[Test]
#[Covers(DependencyResolverService::class)]
final class DependencyResolverServiceTest
{
    public function resolvesDependenciesBeforeDependents(): void
    {
        $http = PluginFactory::plugin('http', ['server', 'logger']);
        $resolver = $this->resolver([
            $http,
            PluginFactory::plugin('server', ['logger']),
            PluginFactory::plugin('logger'),
        ]);

        $result = $resolver->resolveDependencies([$http]);

        Assert::true($result->isValid);
        Assert::same($result->conflicts, []);
        Assert::same($this->names($result->requiredPlugins), ['logger', 'server', 'http']);
    }

    public function resolvesSharedDependencyOnce(): void
    {
        $http = PluginFactory::plugin('http', ['logger']);
        $jobs = PluginFactory::plugin('jobs', ['logger']);
        $resolver = $this->resolver([$http, $jobs, PluginFactory::plugin('logger')]);

        $result = $resolver->resolveDependencies([$http, $jobs]);

        Assert::true($result->isValid);
        Assert::array($this->names($result->requiredPlugins))->sameElementsAs(['logger', 'http', 'jobs']);
    }

    public function reportsMissingDependency(): void
    {
        $http = PluginFactory::plugin('http', ['logger']);
        $resolver = $this->resolver([$http]);

        $result = $resolver->resolveDependencies([$http]);

        Assert::false($result->isValid);
        Assert::count($result->conflicts, 1);
        Assert::same($result->conflicts[0]->pluginName, 'http');
        Assert::same($result->conflicts[0]->conflictType, ConflictType::MissingDependency);
        Assert::string($result->conflicts[0]->message)->contains("'logger'");
    }

    public function reportsCircularDependency(): void
    {
        $a = PluginFactory::plugin('a', ['b']);
        $resolver = $this->resolver([$a, PluginFactory::plugin('b', ['a'])]);

        $result = $resolver->resolveDependencies([$a]);

        Assert::false($result->isValid);
        Assert::count($result->conflicts, 1);
        Assert::same($result->conflicts[0]->conflictType, ConflictType::CircularDependency);
        Assert::same($result->conflicts[0]->severity, ConflictSeverity::Error);
        Assert::array($result->conflicts[0]->conflictingPlugins)->sameElementsAs(['a', 'b']);
    }

    #[Skip('Bug: a failed resolution leaves its plugin in $visited, so a later plugin depending on it is reported as circular')]
    public function failedResolutionDoesNotAffectNextPlugin(): void
    {
        $broken = PluginFactory::plugin('broken', ['missing']);
        $dependent = PluginFactory::plugin('dependent', ['broken']);
        $resolver = $this->resolver([$broken, $dependent]);

        $result = $resolver->resolveDependencies([$broken, $dependent]);

        Assert::count($result->conflicts, 2);
        Assert::same($result->conflicts[1]->conflictType, ConflictType::MissingDependency);
    }

    public function detectsVersionConflict(): void
    {
        $resolver = $this->resolver([]);

        $conflicts = $resolver->detectConflicts([
            PluginFactory::plugin('http', ref: 'v5.0.0'),
            PluginFactory::plugin('http', ref: 'v5.0.0'),
            PluginFactory::plugin('http', ref: 'v5.1.0'),
        ]);

        Assert::count($conflicts, 1);
        Assert::same($conflicts[0]->conflictType, ConflictType::VersionConflict);
        Assert::string($conflicts[0]->message)->contains('v5.0.0')->contains('v5.1.0');
    }

    public function validatesPluginCombination(): void
    {
        $resolver = $this->resolver([]);

        Assert::true($resolver->validatePluginCombination([
            PluginFactory::plugin('http'),
            PluginFactory::plugin('grpc'),
        ]));
        Assert::false($resolver->validatePluginCombination([
            PluginFactory::plugin('http', ref: 'v5.0.0'),
            PluginFactory::plugin('http', ref: 'v5.1.0'),
        ]));
    }

    public function warnsAboutTooManyJobDrivers(): void
    {
        $resolver = $this->resolver([]);
        $drivers = \array_map(
            static fn(string $name): Plugin => PluginFactory::plugin($name, category: PluginCategory::Jobs),
            ['jobs', 'amqp', 'sqs', 'nats'],
        );

        Assert::same($resolver->detectConflicts($drivers), []);

        $drivers[] = PluginFactory::plugin('kafka', category: PluginCategory::Jobs);
        $conflicts = $resolver->detectConflicts($drivers);

        Assert::count($conflicts, 1);
        Assert::same($conflicts[0]->conflictType, ConflictType::ResourceConflict);
        Assert::same($conflicts[0]->severity, ConflictSeverity::Warning);
        Assert::array($conflicts[0]->conflictingPlugins)->sameElementsAs(['amqp', 'sqs', 'nats', 'kafka']);
        Assert::true($resolver->validatePluginCombination($drivers));
    }

    public function suggestsStableVersions(): void
    {
        $resolver = $this->resolver([]);

        $suggestions = $resolver->suggestCompatibleVersions([
            PluginFactory::plugin('http', ref: 'v4.3.0'),
            PluginFactory::plugin('grpc', ref: 'master'),
            PluginFactory::plugin('logger', ref: 'v5.0.2'),
        ]);

        Assert::count($suggestions, 2);
        Assert::same($suggestions[0]->pluginName, 'http');
        Assert::same($suggestions[0]->currentVersion, 'v4.3.0');
        Assert::same($suggestions[0]->suggestedVersion, 'v5.0.2');
        Assert::same($suggestions[1]->pluginName, 'grpc');
        Assert::string($suggestions[1]->reason)->contains('master branch');
    }

    public function serializesConflict(): void
    {
        $conflict = new ConflictInfo('http', ConflictType::VersionConflict, 'message', ['http']);

        Assert::same($conflict->jsonSerialize(), [
            'plugin_name' => 'http',
            'conflict_type' => 'version_conflict',
            'message' => 'message',
            'conflicting_plugins' => ['http'],
            'severity' => 'error',
        ]);
    }

    /**
     * @param array<Plugin> $plugins
     */
    private function resolver(array $plugins): DependencyResolverService
    {
        return new DependencyResolverService(new ConfigPluginProvider($plugins));
    }

    /**
     * @param array<Plugin> $plugins
     * @return list<string>
     */
    private function names(array $plugins): array
    {
        return \array_values(\array_map(static fn(Plugin $plugin): string => $plugin->name, $plugins));
    }
}
