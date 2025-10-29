<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Assert\State\ExpectedException;
use Testo\Assert\State\ExpectedFailure;
use Testo\Assert\State\Record;

/**
 * Collects assertions.
 */
final class TestState
{
    /**
     * @var list<Record> The history of assertions.
     */
    public array $history = [];

    /**
     * @var ExpectedException|null Expected exception configuration.
     */
    public ?ExpectedException $expectException = null;

    /**
     * @var ExpectedFailure|null Expected failure configuration.
     */
    public ?ExpectedFailure $expectedFailure = null;

    public \WeakMap $weakMap;

    public function __construct()
    {
        $this->weakMap = new \WeakMap();
    }
}
