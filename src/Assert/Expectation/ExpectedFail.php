<?php

declare(strict_types=1);

namespace Testo\Assert\Expectation;

use Testo\Assert\State\AssertException;
use Testo\Assert\TestState;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestResult;

/**
 * If {@see Assert::fail()} was called but the exception was caught in the test and the test
 * ended successfully without throwing this is suspicious behavior, mark the test as Risky.
 *
 * todo: create Fail exception
 */
final class ExpectedFail
{
    public function __construct(
        public readonly AssertException $fail,
    ) {}

    public function __invoke(TestResult $result, TestState $state): TestResult
    {

        // todo: add warning that the Fail has not been thrown
        // $this->fail !== $result->failure and $result = $result->withWarning($this->fail);

        return $result->status->isCompleted() && $this->fail !== $result->failure
            ? $result->with(status: Status::Risky)->withFailure($this->fail)
            : $result;
    }
}
