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

    /**
     * Counts tests by specific status.
     *
     * @return int<0, max>
     */
    public function countTests(Status $status): int
    {
        $count = 0;

        foreach ($this->results as $testResult) {
            $testResult->status === $status and $count++;
        }

        return $count;
    }

    /**
     * Counts the number of failed tests.
     *
     * @return int<0, max>
     */
    public function countFailedTests(): int
    {
        $count = 0;

        foreach ($this->results as $testResult) {
            $testResult->status->isFailure() and $count++;
        }

        return $count;
    }

    /**
     * Counts the number of passed tests.
     *
     * @return int<0, max>
     */
    public function countPassedTests(): int
    {
        $count = 0;

        foreach ($this->results as $testResult) {
            $testResult->status->isSuccessful() and $count++;
        }

        return $count;
    }
}
