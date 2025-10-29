<?php

declare(strict_types=1);

namespace Testo\Assert\Interceptor;

use Testo\Assert\State\AssertException;
use Testo\Assert\StaticState;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * @note Must be placed right before the test execution.
 */
final class ObjectTrackerInterceptor implements TestRunInterceptor
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
        $map = $context?->weakMap;

        # No state or expectation defined
        if ($map === null || $map->count() === 0) {
            return $result;
        }

        $e = AssertException::leaks($map);
        $context->history[] = $e;
        return $result->with(status: Status::Failed)->withFailure($e);
    }
}
