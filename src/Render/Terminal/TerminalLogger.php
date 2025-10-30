<?php

declare(strict_types=1);

namespace Testo\Render\Terminal;

use Testo\Assert\TestState;
use Testo\Render\Helper;
use Testo\Sample\MultipleResult;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\SuiteInfo;
use Testo\Test\Dto\SuiteResult;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Terminal logger for test reporting with configurable output format.
 *
 * @internal
 */
final class TerminalLogger
{
    /** @var int<0, max> */
    private int $totalTests = 0;

    /** @var int<0, max> */
    private int $passedTests = 0;

    /** @var int<0, max> */
    private int $failedTests = 0;

    /** @var int<0, max> */
    private int $skippedTests = 0;

    /** @var int<0, max> */
    private int $riskyTests = 0;

    /** @var list<array{result: TestResult, duration: int<0, max>|null}> */
    private array $failures = [];

    private float $startTime;
    private bool $headerPrinted = false;

    public function __construct(
        private readonly OutputFormat $format = OutputFormat::Compact,
    ) {
        $this->startTime = \microtime(true);
    }

    /**
     * Publishes test suite started message.
     */
    public function suiteStartedFromInfo(SuiteInfo $info): void
    {
        $this->ensureHeader();
        echo Formatter::suiteHeader($info->name, $this->format);
    }

    /**
     * Handles test suite result.
     */
    public function handleSuiteResult(SuiteInfo $info, SuiteResult $result): void
    {
        echo Formatter::suiteSummary($result, $this->format);
    }

    /**
     * Publishes test case started message.
     */
    public function caseStartedFromInfo(CaseInfo $info): void
    {
        $this->ensureHeader();
        echo Formatter::caseHeader($info->name, $this->format);
    }

    /**
     * Handles test case result.
     */
    public function handleCaseResult(CaseInfo $info, CaseResult $result): void
    {
        echo Formatter::caseFooter($this->format);
        echo Formatter::caseSummary($result, $this->format);
    }

    /**
     * Publishes test started message.
     */
    public function testStartedFromInfo(TestInfo $info): void
    {
        $this->ensureHeader();
        // No output on test start for compact/dots mode
    }

    /**
     * Handles test result and updates statistics.
     *
     * @param int<0, max>|null $duration Duration in milliseconds
     */
    public function handleTestResult(TestResult $result, ?int $duration): void
    {
        $this->totalTests++;

        match ($result->status) {
            Status::Passed, Status::Flaky => $this->handlePassedTest($result, $duration),
            Status::Failed, Status::Error, Status::Aborted => $this->handleFailedTest($result, $duration),
            Status::Skipped, Status::Cancelled => $this->handleSkippedTest($result, $duration),
            Status::Risky => $this->handleRiskyTest($result, $duration),
        };
    }

    /**
     * Prints final summary with all failures and statistics.
     */
    public function printSummary(): void
    {
        $this->printFailures();
        $this->printStatistics();
    }

    /**
     * Ensures run header is printed once.
     */
    private function ensureHeader(): void
    {
        if ($this->headerPrinted) {
            return;
        }

        echo Formatter::runHeader();
        $this->headerPrinted = true;
    }

    /**
     * Handles passed test status.
     *
     * @param int<0, max>|null $duration
     */
    private function handlePassedTest(TestResult $result, ?int $duration): void
    {
        $this->passedTests++;

        $item = new FormattedItem(
            name: $result->info->name,
            status: $result->status,
            duration: $duration,
            indentLevel: 0,
        );

        echo Formatter::formatRun($item, $this->format);
        $this->printMultipleRuns($result);
    }

    /**
     * Handles failed test status.
     *
     * @param int<0, max>|null $duration
     */
    private function handleFailedTest(TestResult $result, ?int $duration): void
    {
        $this->failedTests++;
        $this->failures[] = ['result' => $result, 'duration' => $duration];

        $item = new FormattedItem(
            name: $result->info->name,
            status: $result->status,
            duration: $duration,
            indentLevel: 0,
            description: (string) $result->getAttribute('description'),
        );

        echo Formatter::formatRun($item, $this->format);
        $this->printMultipleRuns($result);
        $this->printAssertionHistory($result);
    }

    /**
     * Prints multiple test runs if available.
     */
    private function printMultipleRuns(TestResult $result): void
    {
        if ($this->format === OutputFormat::Dots) {
            return;
        }

        $multipleResult = $result->getAttribute(MultipleResult::class);

        if ($multipleResult === null) {
            return;
        }

        $runNumber = 1;
        foreach ($multipleResult->results as $runKey => $runResult) {
            $item = new FormattedItem(
                name: "Run #{$runNumber}",
                status: $runResult->status,
                duration: null,
                indentLevel: 1,
                description: (string) $runKey,
            );

            echo Formatter::formatRun($item, $this->format);
            $runNumber++;
        }
    }

    /**
     * Prints assertion history for a test result if available.
     */
    private function printAssertionHistory(TestResult $result): void
    {
        $testState = $result->getAttribute(TestState::class);

        if ($testState === null || $testState->history === []) {
            return;
        }

        echo Formatter::assertionHistoryHeader($this->format);

        foreach ($testState->history as $assertion) {
            echo Formatter::assertionLine($assertion, $this->format);
        }
    }

    /**
     * Handles skipped test status.
     *
     * @param int<0, max>|null $duration
     */
    private function handleSkippedTest(TestResult $result, ?int $duration): void
    {
        $this->skippedTests++;

        $item = new FormattedItem(
            name: $result->info->name,
            status: $result->status,
            duration: $duration,
            indentLevel: 0,
        );

        echo Formatter::formatRun($item, $this->format);
    }

    /**
     * Handles risky test status.
     *
     * @param int<0, max>|null $duration
     */
    private function handleRiskyTest(TestResult $result, ?int $duration): void
    {
        $this->riskyTests++;

        $item = new FormattedItem(
            name: $result->info->name,
            status: $result->status,
            duration: $duration,
            indentLevel: 0,
        );

        echo Formatter::formatRun($item, $this->format);
    }

    /**
     * Prints all failures with details.
     */
    private function printFailures(): void
    {
        if ($this->failures === []) {
            return;
        }

        echo Formatter::failuresHeader();

        $index = 1;
        foreach ($this->failures as $failure) {
            $result = $failure['result'];
            $duration = $failure['duration'];
            $throwable = $result->failure;

            $message = $throwable?->getMessage() ?? 'Test failed';
            $details = $throwable !== null ? Helper::formatThrowable($throwable) : '';

            echo Formatter::failureDetail(
                $index,
                $result->info->name,
                $message,
                $details,
                $duration,
            );

            $index++;
        }
    }

    /**
     * Prints final statistics.
     */
    private function printStatistics(): void
    {
        $duration = \microtime(true) - $this->startTime;
        $success = $this->failedTests === 0;

        echo Formatter::summary(
            $this->totalTests,
            $this->passedTests,
            $this->failedTests,
            $this->skippedTests,
            $this->riskyTests,
            $duration,
        );

        echo Formatter::finalBanner($success);
    }
}
