<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Environment;

use App\Module\Velox\Environment\Exception\EnvironmentFileException;
use App\Module\Velox\Environment\Service\EnvironmentFileService;
use Spiral\Files\Files;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
#[Covers(EnvironmentFileService::class)]
final class EnvironmentFileServiceTest
{
    private Files $files;
    private string $directory;
    private string $envFile;

    public function __construct()
    {
        $this->files = new Files();
    }

    #[BeforeTest]
    public function createDirectory(): void
    {
        $this->directory = \sys_get_temp_dir() . '/velox-env-test-' . \bin2hex(\random_bytes(6));
        $this->files->ensureDirectory($this->directory);
        $this->envFile = $this->directory . '/.env';
    }

    #[AfterTest]
    public function removeDirectory(): void
    {
        $this->files->deleteDirectory($this->directory);
    }

    public function readsMissingFileAsEmpty(): void
    {
        $service = $this->service();

        Assert::same($service->readEnvironmentFile(), []);
        Assert::false($service->hasEnvironmentVariable('APP_ENV'));
        Assert::null($service->getEnvironmentVariable('APP_ENV'));
    }

    public function parsesVariables(): void
    {
        $this->files->write($this->envFile, \implode("\n", [
            '# comment',
            '',
            'APP_ENV=local',
            '  SPACED = value  ',
            'QUOTED="hello world"',
            "SINGLE='single'",
            'URL=https://example.com/?a=b',
            'RR_PLUGIN_HTTP=v5',
            'invalid line',
        ]));
        $service = $this->service();

        Assert::same($service->readEnvironmentFile(), [
            'APP_ENV' => 'local',
            'SPACED' => 'value',
            'QUOTED' => 'hello world',
            'SINGLE' => 'single',
            'URL' => 'https://example.com/?a=b',
            'RR_PLUGIN_HTTP' => 'v5',
        ]);
        Assert::same($service->getPluginEnvironmentVariables(), ['RR_PLUGIN_HTTP' => 'v5']);
        Assert::true($service->hasEnvironmentVariable('SINGLE'));
    }

    public function writesGroupedVariables(): void
    {
        $service = $this->service(createBackup: false);

        $service->writeEnvironmentFile([
            'APP_ENV' => 'local',
            'GITHUB_TOKEN' => 'token',
            'RR_PLUGIN_HTTP' => 'v5',
            'GITLAB_TOKEN' => 'gitlab',
            'MESSAGE' => 'hello world',
        ]);

        Assert::string($this->files->read($this->envFile))
            ->startsWith('# Environment Configuration')
            ->contains("# RR_PLUGIN\nRR_PLUGIN_HTTP=v5\n\n# GITHUB\nGITHUB_TOKEN=token\n\n# GITLAB\nGITLAB_TOKEN=gitlab\n\nAPP_ENV=local\nMESSAGE=\"hello world\"\n");
        Assert::same($service->getEnvironmentVariable('MESSAGE'), 'hello world');
    }

    public function updatesVariablesAndKeepsBackup(): void
    {
        $this->files->write($this->envFile, "APP_ENV=local\nDEBUG=true\n");
        $service = $this->service();

        $service->updateEnvironmentVariable('APP_ENV', 'production');
        $service->updateEnvironmentVariables(['DEBUG' => 'false', 'NEW' => 'value']);

        Assert::same($service->readEnvironmentFile(), ['APP_ENV' => 'production', 'DEBUG' => 'false', 'NEW' => 'value']);
        Assert::notBlank(\glob($this->envFile . '.backup.*'));
    }

    public function restoresLatestBackup(): void
    {
        $this->files->write($this->envFile, "APP_ENV=local\n");
        $service = $this->service();
        $service->createBackupFile();
        $this->files->write($this->envFile, "APP_ENV=broken\n");

        $service->restoreFromBackup();

        Assert::same($service->getEnvironmentVariable('APP_ENV'), 'local');
    }

    public function failsToRestoreWithoutBackup(): never
    {
        Expect::exception(EnvironmentFileException::class)->withMessage('No backup files found');

        $this->service()->restoreFromBackup();
    }

    public function skipsBackupOfMissingFile(): void
    {
        $this->service()->createBackupFile();

        Assert::same(\glob($this->envFile . '.backup.*'), []);
    }

    private function service(bool $createBackup = true): EnvironmentFileService
    {
        return new EnvironmentFileService($this->files, $this->envFile, $createBackup);
    }
}
