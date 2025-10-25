<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

/**
 * Result of running tests.
 *
 * @implements \IteratorAggregate<TestResult>
 */
final class CaseResult implements \IteratorAggregate
{
    public function __construct(
        /**
         * Test result collection.
         *
         * @var iterable<TestResult>
         */
        public readonly iterable $results,

        public readonly Status $status,
    ) {}

    public function getIterator(): \Traversable
    {
        yield from $this->results;
    }
}
