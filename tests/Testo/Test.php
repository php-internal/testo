<?php

declare(strict_types=1);

namespace Tests\Testo;

use Testo\Attribute;

final class Test
{
    #[Attribute\Test]
    public function testFirst(): void
    {
        echo 'Run test ' . __METHOD__ . PHP_EOL;
    }
}
