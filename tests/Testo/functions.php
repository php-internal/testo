<?php

declare(strict_types=1);

namespace Tests\Testo;

use Testo\Assert;
use Testo\Attribute\Test;

#[Test]
function simpleAssertions(): void
{
    Assert::same(1, 1);
    Assert::null(null);
    Assert::notSame(42, '42');
}
