<?php

declare(strict_types=1);

namespace Testo\Dto\Run;

use Testo\Suite\Dto\SuiteResult;

/**
 * Result of running tests.
 *
 * @implements \IteratorAggregate<SuiteResult>
 */
final class RunResult implements \IteratorAggregate
{
    public function __construct(
        /**
         * Test result collection.
         *
         * @var iterable<SuiteResult>
         */
        public readonly iterable $results,
    ) {}

    public function getIterator(): \Traversable
    {
        yield from $this->results;
    }
}
