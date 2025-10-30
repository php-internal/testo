<?php

declare(strict_types=1);

namespace Tests\Testo\Assert\Feature;

use Testo\Assert;
use Testo\Attribute\Test;
use Testo\Test\Dto\Status;
use Tests\Testo\Assert\Helper;
use Tests\Testo\Assert\Stub\Common;

final class CommonTest
{
    #[Test(description: 'A successfully finished test without any assertion marked as Risky')]
    public function noAssertions(): void
    {
        $result = Helper::runTest([Common::class, 'risky']);
        Assert::same(Status::Risky, $result->status);
    }
}
