<?php

declare(strict_types=1);

namespace Tests\Testo;

use Testo\Assert;
use Testo\Attribute;

final class Test
{
    #[Attribute\Test]
    public function simpleAssertions(): void
    {
        Assert::same(1, 1);
        Assert::null(null);
    }

    #[Attribute\Test]
    public function notSame(): void
    {
        Assert::same(1, 2);
    }

    #[Attribute\Test]
    public function expectException(): void
    {
        Assert::exception(\Throwable::class);

        throw new \RuntimeException('This is an expected exception.');
    }
}
