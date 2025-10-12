<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Dto\Test\TestResult;
use Testo\Interceptor\RunTest\Input;
use Testo\Interceptor\Internal\InterceptorMarker;

/**
 * Interceptor for running tests.
 */
interface RunTestInterceptor extends InterceptorMarker
{
    public function runTest(Input $dto, callable $next): TestResult;
}
