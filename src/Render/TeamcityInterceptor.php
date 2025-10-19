<?php

declare(strict_types=1);

namespace Testo\Render;

use Testo\Interceptor\TestRunInterceptor;
use Testo\Interceptor\TestCaseRunInterceptor;
use Testo\Interceptor\TestSuiteRunInterceptor;
use Testo\Render\Teamcity\TeamcityLogger;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\SuiteInfo;
use Testo\Test\Dto\SuiteResult;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

final class TeamcityInterceptor implements
    StdoutRenderer,
    TestRunInterceptor,
    TestCaseRunInterceptor,
    TestSuiteRunInterceptor
{
    public function __construct(
        private readonly TeamcityLogger $logger,
    ) {}

    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $this->logger->testStartedFromInfo($info);

        $start = \microtime(true);
        /** @var TestResult $result */
        $result = $next($info);
        $duration = (int) \round((\microtime(true) - $start) * 1000);

        $this->logger->handleTestResult($result, $duration);
        return $result;
    }

    public function runTestCase(CaseInfo $info, callable $next): CaseResult
    {
        $this->logger->caseStartedFromInfo($info);

        /** @var CaseResult $result */
        $result = $next($info);

        $this->logger->handleCaseResult($info, $result);
        return $result;
    }

    public function runTestSuite(SuiteInfo $info, callable $next): SuiteResult
    {
        $this->logger->suiteStartedFromInfo($info);

        /** @var SuiteResult $result */
        $result = $next($info);
        $this->logger->handleSuiteResult($info, $result);

        return $result;
    }
}
