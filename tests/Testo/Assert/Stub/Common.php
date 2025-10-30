<?php

declare(strict_types=1);

namespace Tests\Testo\Assert\Stub;

use Testo\Attribute\Test;

final class Common
{
    #[Test]
    public function risky(): void
    {
        // No assertions here
    }
}
