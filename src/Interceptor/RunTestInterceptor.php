<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Interceptor\RunTest\Input;
use Testo\Interceptor\Internal\InterceptorMarker;
use Testo\Interceptor\RunTest\Output;

/**
 * Interceptor for running tests.
 */
interface RunTestInterceptor extends InterceptorMarker
{
    public function runTest(Input $dto, callable $next): Output;
}
