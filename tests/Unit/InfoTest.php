<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Testo\Common\Info;

final class InfoTest extends TestCase
{
    #[DoesNotPerformAssertions]
    public function testVersionDoesntFail(): void
    {
        Info::version();
    }
}
