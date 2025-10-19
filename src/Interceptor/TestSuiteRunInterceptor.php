<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Module\Interceptor\Internal\InterceptorMarker;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\SuiteInfo;
use Testo\Test\Dto\SuiteResult;

/**
 * Intercept running a test suite.
 *
 * @extends InterceptorMarker<CaseInfo, CaseResult>
 */
interface TestSuiteRunInterceptor extends InterceptorMarker
{
    /**
     * @param SuiteInfo $info Test suite to run.
     * @param callable(SuiteInfo): SuiteResult $next Next interceptor or core logic to run the test suite.
     */
    public function runTestSuite(SuiteInfo $info, callable $next): SuiteResult;
}
