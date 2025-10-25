<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

/**
 * Result of running tests.
 *
 * @implements \IteratorAggregate<CaseResult>
 */
final class SuiteResult implements \IteratorAggregate
{
    public function __construct(
        /**
         * Test result collection.
         *
         * @var iterable<CaseResult>
         */
        public readonly iterable $results,

        public readonly Status $status,
    ) {}

    /**
     * @return \Traversable<CaseResult>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->results;
    }
}
