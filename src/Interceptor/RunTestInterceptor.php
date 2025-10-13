<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Interceptor\Internal\InterceptorMarker;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Interceptor for running tests.
 */
interface RunTestInterceptor extends InterceptorMarker
{
    /**
     * @param TestInfo $dto Information about the test to be run.
     * @param callable(TestInfo): TestResult $next Next interceptor or core logic to run the test.
     * @return TestResult
     */
    public function runTest(TestInfo $dto, callable $next): TestResult;
}
