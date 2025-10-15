<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Interceptor\Internal\InterceptorMarker;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Interceptor for running tests.
 *
 * @extends InterceptorMarker<TestInfo, TestResult>
 */
interface TestCallInterceptor extends InterceptorMarker
{
    /**
     * @param TestInfo $info Information about the test to be run.
     * @param callable(TestInfo): TestResult $next Next interceptor or core logic to run the test.
     */
    public function runTest(TestInfo $info, callable $next): TestResult;
}
