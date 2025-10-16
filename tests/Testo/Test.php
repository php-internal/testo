<?php

declare(strict_types=1);

namespace Tests\Testo;

use Testo\Assert\Assert;
use Testo\Attribute;

final class Test
{
    #[Attribute\Test]
    public function testFirst(): void
    {
        Assert::same(1, 1);
        Assert::same(1, 2);
    }
}
