<?php

declare(strict_types=1);

namespace Testo\Assert\Interceptor;

use Testo\Assert\State\AssertException;
use Testo\Assert\State\ExpectedFailure;
use Testo\Assert\State\Record;
use Testo\Assert\State\Success;
use Testo\Assert\StaticState;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Interceptor to handle expected failures.
 *
 * @note Must be placed right before the test execution.
 */
final class FailInterceptor implements TestRunInterceptor
{
    /**
     * @throws AssertException When the expected failure is not thrown or has wrong message.
     */
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        /** @var TestResult $result */
        $result = $next($info);
        $context = StaticState::current();
        $expectation = $context?->expectedFailure;

        # No state or expectation defined
        if ($expectation === null) {
            # Test failed due to an assertion failure
            if ($result->status->isFailure() && $result->failure instanceof AssertException) {
                $result = $result->with(status: Status::Failed);
            }

            return $result;
        }

        # An expectation was defined
        # Check if the expectation was met
        $record = self::isPassed($expectation, $result->failure);
        $context->history[] = $record;
        $context->expectedFailure = null;

        return $record->isSuccess()
            ? $result->with(status: Status::Passed)
            : $result->with(status: Status::Failed)->withFailure($record);
    }

    private static function isPassed(ExpectedFailure $expected, ?\Throwable $actual): Record|AssertException
    {
        # If no failure occurred but one was expected
        if ($actual === null) {
            return AssertException::fail('Expected test to fail, but it passed');
        }

        # If failure is not an AssertException
        if (!$actual instanceof AssertException) {
            return AssertException::fail('Expected AssertException failure, but got ' . $actual::class);
        }

        # If any failure is acceptable (no specific message expected)
        if ($expected->message === null) {
            return new Success(
                assertion: 'Test failed as expected',
            );
        }

        # Check if the failure message matches exactly
        if ($actual->assertion === $expected->message) {
            return new Success(
                assertion: 'Test failed with expected message: `' . $expected->message . '`',
            );
        }

        # Wrong failure message
        return AssertException::fail(
            'Expected failure message: `' . $expected->message . '`, but got: `' . $actual->assertion . '`'
        );
    }
}