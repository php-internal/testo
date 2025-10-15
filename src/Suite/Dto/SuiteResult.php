<?php

declare(strict_types=1);

namespace Testo\Suite\Dto;

use Testo\Test\Dto\CaseResult;

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
    ) {}

    /**
     * @return \Traversable<CaseResult>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->results;
    }
}
