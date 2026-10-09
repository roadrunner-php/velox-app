<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\Version;

use App\Module\Velox\Version\Service\VersionComparisonService;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Data\DataSet;
use Testo\Test;

#[Test]
#[Covers(VersionComparisonService::class)]
final class VersionComparisonServiceTest
{
    private VersionComparisonService $service;

    public function __construct()
    {
        $this->service = new VersionComparisonService();
    }

    #[DataSet(['v1.2.3', '1.2.3', 0], 'prefix is ignored')]
    #[DataSet(['v1.2', 'v1.2.0', 0], 'missing patch is zero')]
    #[DataSet(['v1.10.0', 'v1.9.9', 1], 'numeric minor comparison')]
    #[DataSet(['v2.0.0', 'v10.0.0', -1], 'numeric major comparison')]
    #[DataSet(['main', 'v5.2.7', 1], 'branch is newer than a release')]
    #[DataSet(['v1.2.3.4', 'v1.2.3', 0], 'extra parts are dropped')]
    public function comparesVersions(string $left, string $right, int $expected): void
    {
        Assert::same($this->service->compareVersions($left, $right), $expected);
    }

    public function branchIsNewerThanCalendarRelease(): void
    {
        Assert::same($this->service->compareVersions('master', 'v2025.1.1'), 1);
    }

    public function headIsTreatedAsBranch(): void
    {
        Assert::same($this->service->compareVersions('HEAD', 'v5.0.0'), 1);
    }

    public function detectsNewerVersion(): void
    {
        Assert::true($this->service->isNewerVersion('v1.0.0', 'v1.0.1'));
        Assert::false($this->service->isNewerVersion('v1.0.1', 'v1.0.0'));
        Assert::false($this->service->isNewerVersion('v1.0.0', 'v1.0.0'));
    }

    #[DataSet(['v1.0.0', true], 'release')]
    #[DataSet(['v1.0.0-beta.1', false], 'beta')]
    #[DataSet(['v1.0.0-RC1', false], 'release candidate')]
    #[DataSet(['v1.0.0-alpha', false], 'alpha')]
    #[DataSet(['v1.0.0+build.5', false], 'build metadata')]
    public function detectsStableVersion(string $version, bool $expected): void
    {
        Assert::same($this->service->isStableVersion($version), $expected);
    }

    public function picksLatestStableVersion(): void
    {
        $latest = $this->service->getLatestStableVersion(['v1.2.0', 'v1.10.0', 'v2.0.0-beta', 'v1.9.5']);

        Assert::same($latest, 'v1.10.0');
    }

    public function returnsNullWithoutStableVersions(): void
    {
        Assert::null($this->service->getLatestStableVersion(['v2.0.0-beta', 'v2.0.0-rc1']));
        Assert::null($this->service->getLatestStableVersion([]));
    }

    #[DataSet(['v1.2.3', true], 'with prefix')]
    #[DataSet(['1.2.3', true], 'without prefix')]
    #[DataSet(['1.2.3-beta.1+build.7', true], 'pre-release and build')]
    #[DataSet(['1.2', false], 'missing patch')]
    #[DataSet(['master', false], 'branch')]
    public function validatesSemanticVersion(string $version, bool $expected): void
    {
        Assert::same($this->service->isValidSemanticVersion($version), $expected);
    }

    public function extractsMajorVersion(): void
    {
        Assert::same($this->service->getMajorVersion('v5.2.7'), 5);
        Assert::same($this->service->getMajorVersion('2025.1'), 2025);
        Assert::true($this->service->areVersionsCompatible('v5.0.0', 'v5.9.1'));
        Assert::false($this->service->areVersionsCompatible('v4.9.0', 'v5.0.0'));
    }

    #[DataSet(['v5.0.0', 'v5.0.0', 'none', false], 'up to date')]
    #[DataSet(['v5.1.0', 'v5.0.0', 'none', false], 'current is newer')]
    #[DataSet(['v4.9.0', 'v5.0.0', 'major', false], 'major update')]
    #[DataSet(['v5.0.0', 'v5.2.1', 'minor_patch', true], 'compatible update')]
    public function recommendsUpdate(string $current, string $latest, string $type, bool $recommended): void
    {
        $recommendation = $this->service->getUpdateRecommendation($current, $latest);

        Assert::same($recommendation['updateType'], $type);
        Assert::same($recommendation['recommended'], $recommended);
        Assert::string($recommendation['reason'])->notContains('could not be determined');
    }
}
