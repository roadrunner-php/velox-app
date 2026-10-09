<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Plugin;

use App\Module\Velox\Plugin\Discovery\Exception\ManifestValidationException;
use App\Module\Velox\Plugin\Discovery\Service\ManifestParserService;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Data\DataSet;
use Testo\Expect;
use Testo\Test;

#[Test]
#[Covers(ManifestParserService::class)]
final class ManifestParserServiceTest
{
    public function parsesManifest(): void
    {
        $yaml = <<<'YAML'
            name: my-plugin
            description: Does something useful
            category: kv
            dependencies: [server, logger]
            folder: plugin
            docsUrl: https://example.com/docs
            license: MIT
            keywords: [cache]
            YAML;

        $manifest = (new ManifestParserService())->parse($yaml, 'acme', 'rr-my-plugin', 'v1.2.3');

        Assert::same($manifest->name, 'my-plugin');
        Assert::same($manifest->owner, 'acme');
        Assert::same($manifest->repository, 'rr-my-plugin');
        Assert::same($manifest->version, 'v1.2.3');
        Assert::same($manifest->category, PluginCategory::Kv);
        Assert::same($manifest->dependencies, ['server', 'logger']);
        Assert::same($manifest->folder, 'plugin');
        Assert::same($manifest->docsUrl, 'https://example.com/docs');
        Assert::same($manifest->license, 'MIT');
        Assert::same($manifest->keywords, ['cache']);
        Assert::same($manifest->repositoryType, 'github');
    }

    public function rejectsInvalidYaml(): never
    {
        Expect::exception(ManifestValidationException::class)->withMessageContaining('Invalid YAML syntax');

        (new ManifestParserService())->parse("name: [unclosed", 'acme', 'repo', 'v1.0.0');
    }

    public function rejectsScalarManifest(): never
    {
        Expect::exception(ManifestValidationException::class)->withMessage('Manifest must be a YAML object');

        (new ManifestParserService())->parse('just a string', 'acme', 'repo', 'v1.0.0');
    }

    #[DataSet([['description' => 'Long enough text', 'category' => 'kv'], 'Missing required field: name'], 'missing name')]
    #[DataSet([['name' => 'my-plugin', 'category' => 'kv'], 'Missing required field: description'], 'missing description')]
    #[DataSet([['name' => 'my-plugin', 'description' => 'Long enough text'], 'Missing required field: category'], 'missing category')]
    #[DataSet([['name' => 'My_Plugin', 'description' => 'Long enough text', 'category' => 'kv'], 'Invalid format for field name'], 'bad name')]
    #[DataSet([['name' => 'my-plugin', 'description' => 'short', 'category' => 'kv'], 'Invalid format for field description'], 'short description')]
    #[DataSet([['name' => 'my-plugin', 'description' => 'Long enough text', 'category' => 'unknown'], 'Invalid format for field category'], 'bad category')]
    #[DataSet([['name' => 'my-plugin', 'description' => 'Long enough text', 'category' => 'kv', 'dependencies' => 'server'], 'Invalid format for field dependencies'], 'scalar dependencies')]
    public function rejectsInvalidManifest(array $data, string $message): never
    {
        Expect::exception(ManifestValidationException::class)->withMessageContaining($message);

        (new ManifestParserService())->parse(\json_encode($data, \JSON_THROW_ON_ERROR), 'acme', 'repo', 'v1.0.0');
    }

    public function exposesValidationDetails(): void
    {
        $exception = ManifestValidationException::invalidDependency('repo', 'unknown');

        Assert::same($exception->repository, 'repo');
        Assert::same($exception->errors, ['dependencies' => 'Unknown plugin: unknown']);
    }
}
