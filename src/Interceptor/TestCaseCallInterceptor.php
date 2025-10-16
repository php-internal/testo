<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Interceptor\Internal\InterceptorMarker;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;

/**
 * Intercept running a test case.
 *
 * @extends InterceptorMarker<CaseInfo, CaseResult>
 */
interface TestCaseCallInterceptor extends InterceptorMarker
{
    /**
     * @param CaseInfo $info Test case to run.
     * @param callable(CaseInfo): CaseResult $next Next interceptor or core logic to run the test case.
     */
    public function runTestCase(CaseInfo $info, callable $next): CaseResult;
}
