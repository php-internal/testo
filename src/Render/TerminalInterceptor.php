<?php

declare(strict_types=1);

namespace Testo\Render;

use Testo\Interceptor\TestCaseRunInterceptor;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Interceptor\TestSuiteRunInterceptor;
use Testo\Render\Terminal\ColorMode;
use Testo\Render\Terminal\OutputFormat;
use Testo\Render\Terminal\Style;
use Testo\Render\Terminal\TerminalLogger;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\SuiteInfo;
use Testo\Test\Dto\SuiteResult;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Terminal interceptor for rendering test results with configurable output.
 *
 * Implements StdoutRenderer to ensure only one stdout renderer is active.
 * Supports multiple output formats (Compact, Verbose, Dots) and color modes.
 */
final class TerminalInterceptor implements
    StdoutRenderer,
    TestRunInterceptor,
    TestCaseRunInterceptor,
    TestSuiteRunInterceptor
{
    public function __construct(
        private readonly TerminalLogger $logger,
        ColorMode $colorMode = ColorMode::Always,
    ) {
        // Configure color support based on mode
        Style::setColorsEnabled($colorMode->shouldUseColors());
    }

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

    /**
     * Prints final summary after all tests complete.
     * Should be called after test execution finishes.
     */
    public function printSummary(): void
    {
        $this->logger->printSummary();
    }
}
