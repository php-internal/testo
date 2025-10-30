<?php

declare(strict_types=1);

namespace Testo\Assert\Interceptor;

use Testo\Assert\State\AssertException;
use Testo\Assert\State\Record;
use Testo\Assert\State\Success;
use Testo\Assert\StaticState;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Interceptor to handle expected exceptions.
 *
 * @note Must be placed right before the test execution.
 */
final class ExpectationsInterceptor implements TestRunInterceptor
{
    /**
     * @throws AssertException When the expected exception is not thrown.
     */
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        /** @var TestResult $result */
        $result = $next($info);

        # Convert Error status to Failed if caused by an assertion failure
        $result->status === Status::Error && $result->failure instanceof AssertException and $result = $result
            ->with(status: Status::Failed);

        $state = StaticState::current();

        # No state available
        if ($state === null) {
            return $result;
        }

        foreach ($state->expectations as $expectation) {
            $result = $expectation($result, $state);
        }

        # Special case: Assert::fail() was called (expectation is AssertException instance)
        # but the exception was caught, so test ended successfully without throwing
        # This is suspicious behavior → mark as Risky
        // if ($result->failure === null
        //     && $expectation->classOrObject instanceof AssertException
        //     && !$record->isSuccess()) {
        //     return $result->with(status: Status::Risky)->withFailure($record);
        // }

        return $result;
    }
}
