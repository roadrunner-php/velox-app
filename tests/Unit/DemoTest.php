<?php

declare(strict_types=1);

namespace Tests\Unit;

use Testo\Assert;
use Testo\Test;

final class DemoTest
{
    #[Test]
    public function demo(): void
    {
        $expected = true;
        $actual = false;

        Assert::true($expected);
        Assert::false($actual);
    }
}
