<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Assert\State\AssertException;
use Testo\Assert\State\Record;
use Testo\Assert\State\Success;
use Testo\Interceptor\TestCallInterceptor;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Interceptor to handle expected exceptions.
 *
 * @note Must be placed right before the test execution.
 */
final class ExpectExceptionInterceptor implements TestCallInterceptor
{
    /**
     * @throws AssertException When the expected exception is not thrown.
     */
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        /** @var TestResult $result */
        $result = $next($info);
        $context = StaticState::current();
        $expectation = $context?->expectException;

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
        $context->expectException = null;

        return $record->isSuccess()
            ? $result->with(status: Status::Passed)
            : $result->with(status: Status::Failed)->withFailure($record);
    }

    private static function isPassed(State\ExpectedException $expected, ?\Throwable $actual): Record|AssertException
    {
        if (!$actual instanceof $expected->class) {
            return AssertException::exceptionClass($expected->class, $actual);
        }

        return new Success(
            assertion: 'Throw exception: `' . $expected->class . '`.',
        );
    }
}
