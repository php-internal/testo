<?php

declare(strict_types=1);

namespace Tests\Testo;

use Testo\Assert;
use Testo\Attribute\ExpectException;
use Testo\Attribute\Test;

final class TestoTest
{
    #[Test]
    public function simpleAssertions(): void
    {
        Assert::same(1, 1);
        Assert::null(null);
    }

    #[Test]
    public function notSame(): void
    {
        Assert::same(1, 2);
    }

    #[Test]
    public function expectException(): never
    {
        Assert::exception(\RuntimeException::class);

        throw new \RuntimeException('This is an expected exception.');
    }

    #[Test]
    #[ExpectException(\RuntimeException::class)]
    public function expectExceptionAttribute(): never
    {
        throw new \RuntimeException('This is an expected exception.');
    }
}
