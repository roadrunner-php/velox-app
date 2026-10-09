<?php

declare(strict_types=1);

namespace Tests\Unit\Module\Velox\BinaryBuilder;

use App\Module\Velox\BinaryBuilder\DTO\BuildResult;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Data\DataSet;
use Testo\Test;

#[Test]
#[Covers(BuildResult::class)]
final class BuildResultTest
{
    #[DataSet([0, '0 B'], 'zero')]
    #[DataSet([512, '512 B'], 'bytes')]
    #[DataSet([1536, '1.5 KB'], 'kilobytes')]
    #[DataSet([52_428_800, '50 MB'], 'megabytes')]
    #[DataSet([5_497_558_138_880, '5120 GB'], 'capped at gigabytes')]
    public function formatsBinarySize(int $bytes, string $expected): void
    {
        $result = new BuildResult(true, '/usr/bin/rr', 1.0, $bytes);

        Assert::same($result->getBinarySize(), $expected);
    }

    public function formatsBuildTime(): void
    {
        $result = new BuildResult(false, '/usr/bin/rr', 12.345, 0);

        Assert::same($result->getBuildTime(), '12.35s');
        Assert::false($result->isSuccess());
    }
}
