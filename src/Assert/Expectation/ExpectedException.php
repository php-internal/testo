<?php

declare(strict_types=1);

namespace Testo\Assert\Expectation;

use Testo\Assert\State\AssertException;
use Testo\Assert\State\Record;
use Testo\Assert\State\Success;
use Testo\Assert\TestState;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestResult;

/**
 * Expected exception declaration.
 */
final class ExpectedException
{
    /**
     * @param class-string|\Throwable $classOrObject Expected exception class, interface, or an object.
     */
    public function __construct(
        public readonly string|\Throwable $classOrObject,
    ) {}

    public function __invoke(TestResult $result, TestState $state): TestResult
    {
        # An expectation was defined
        # Check if the expectation was met
        $record = $this->isPassed($result->failure);
        $state->history[] = $record;

        return $record->isSuccess()
            ? $result->with(status: Status::Passed)
            : $result->with(status: Status::Failed)->withFailure($record);
    }

    private function isPassed(?\Throwable $actual): Record|AssertException
    {
        $class = \is_string($this->classOrObject) ? $this->classOrObject : $this->classOrObject::class;
        if (\is_object($this->classOrObject) ? ($actual === $this->classOrObject) : ($actual instanceof $class)) {
            return new Success(
                assertion: $class === $actual::class
                    ? 'Throw exception: `' . $class . '`.'
                    : 'Throw exception: `' . $class . '` (got `' . $actual::class . '`).',
            );
        }

        return AssertException::exceptionClass($class, $actual);
    }
}
