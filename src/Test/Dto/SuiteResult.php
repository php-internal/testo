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

    /**
     * Counts tests by specific status across all cases in the suite.
     *
     * @return int<0, max>
     */
    public function countTests(Status $status): int
    {
        $count = 0;

        foreach ($this->results as $caseResult) {
            $count += $caseResult->countTests($status);
        }

        return $count;
    }

    /**
     * Counts the number of failed tests across all cases in the suite.
     *
     * @return int<0, max>
     */
    public function countFailedTests(): int
    {
        $count = 0;

        foreach ($this->results as $caseResult) {
            $count += $caseResult->countFailedTests();
        }

        return $count;
    }

    /**
     * Counts the number of passed tests across all cases in the suite.
     *
     * @return int<0, max>
     */
    public function countPassedTests(): int
    {
        $count = 0;

        foreach ($this->results as $caseResult) {
            $count += $caseResult->countPassedTests();
        }

        return $count;
    }
}
