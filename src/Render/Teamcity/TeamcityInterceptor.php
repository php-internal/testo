<?php

declare(strict_types=1);

namespace Testo\Render\Teamcity;

use Testo\Interceptor\TestCallInterceptor;
use Testo\Interceptor\TestCaseCallInterceptor;
use Testo\Render\StdoutRenderer;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

final class TeamcityInterceptor implements StdoutRenderer, TestCallInterceptor, TestCaseCallInterceptor
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
}
