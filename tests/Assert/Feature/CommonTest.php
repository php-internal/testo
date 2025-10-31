<?php

declare(strict_types=1);

namespace Tests\Assert\Feature;

use Testo\Assert;
use Testo\Attribute\Test;
use Testo\Test\Dto\Status;
use Tests\Assert\StubRunner;
use Tests\Assert\Stub\Common;

final class CommonTest
{
    #[Test(description: 'A successfully finished test without any assertion marked as Risky')]
    public function noAssertions(): void
    {
        $result = StubRunner::runTest([Common::class, 'risky']);
        Assert::same(Status::Risky, $result->status);
    }
}
