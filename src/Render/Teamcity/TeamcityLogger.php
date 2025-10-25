<?php

declare(strict_types=1);

namespace Testo\Render\Teamcity;

use Testo\Render\Helper;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\SuiteInfo;
use Testo\Test\Dto\SuiteResult;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * TeamCity logger for test reporting using DTO objects.
 *
 * Publishes TeamCity service messages based on test execution results.
 * Uses TeamcityMessageFormatter for message formatting.
 *
 * @see Formatter for message formatting
 * @internal
 */
final class TeamcityLogger
{
    /**
     * Publishes test suite started message using SuiteInfo.
     */
    public function suiteStartedFromInfo(SuiteInfo $info): void
    {
        $this->publish(Formatter::suiteStarted($info->name));
    }

    /**
     * Publishes test suite finished message using SuiteInfo.
     */
    public function suiteFinishedFromInfo(SuiteInfo $info): void
    {
        $this->publish(Formatter::suiteFinished($info->name));
    }

    /**
     * Handles test suite result.
     *
     * Publishes appropriate TeamCity messages based on suite status.
     */
    public function handleSuiteResult(SuiteInfo $info, SuiteResult $result): void
    {
        // Report suite-level failure if status indicates failure
        if ($result->status->isFailure()) {
            $failedCount = $result->countFailedTests();
            $this->publish(
                Formatter::testStdErr(
                    $info->name,
                    "Test suite failed: {$failedCount} test(s) failed",
                ),
            );
        }

        $this->suiteFinishedFromInfo($info);
    }

    /**
     * Publishes test case started message using CaseInfo.
     *
     * Test case is treated as a suite in TeamCity (a class containing tests).
     */
    public function caseStartedFromInfo(CaseInfo $info): void
    {
        $this->publish(Formatter::suiteStarted($info->name, $info->definition->reflection));
    }

    /**
     * Publishes test case finished message using CaseInfo.
     *
     * Test case is treated as a suite in TeamCity (a class containing tests).
     */
    public function caseFinishedFromInfo(CaseInfo $info): void
    {
        $this->publish(Formatter::suiteFinished($info->name));
    }

    /**
     * Handles test case result.
     *
     * Publishes appropriate TeamCity messages based on case status.
     *
     * @param int<0, max>|null $duration Duration in milliseconds for the case
     */
    public function handleCaseResult(CaseInfo $caseInfo, CaseResult $result, ?int $duration = null): void
    {
        // Report case-level failure if status indicates failure
        if ($result->status->isFailure()) {
            $failedCount = $result->countFailedTests();
            $this->publish(
                Formatter::testStdErr(
                    $caseInfo->name,
                    "Test case failed: {$failedCount} test(s) failed",
                ),
            );
        }

        $this->caseFinishedFromInfo($caseInfo);
    }

    /**
     * Publishes test started message using TestInfo.
     */
    public function testStartedFromInfo(TestInfo $info, bool $captureStandardOutput = false): void
    {
        $this->publish(Formatter::testStarted($info->name, $captureStandardOutput, $info->testDefinition->reflection));
    }

    /**
     * Publishes test finished message using TestInfo.
     *
     * @param int<0, max>|null $duration Duration in milliseconds
     */
    public function testFinishedFromInfo(TestInfo $info, ?int $duration = null): void
    {
        $this->publish(Formatter::testFinished($info->name, $duration));
    }

    /**
     * Publishes test failed message using TestResult.
     */
    public function testFailedFromResult(TestResult $result): void
    {
        $failure = $result->failure;
        $message = $failure?->getMessage() ?? 'Test failed';
        $details = $failure !== null ? Helper::formatThrowable($failure) : '';

        $this->publish(
            Formatter::testFailed(
                name: $result->info->name,
                message: $message,
                details: $details,
            ),
        );
    }

    /**
     * Publishes test ignored message using TestInfo.
     *
     * @param non-empty-string $message Optional skip reason
     */
    public function testIgnoredFromInfo(TestInfo $info, string $message = ''): void
    {
        $this->publish(Formatter::testIgnored($info->name, $message));
    }

    /**
     * Handles test result and publishes appropriate message based on status.
     */
    public function handleTestResult(TestResult $result, ?int $duration = null): void
    {
        match ($result->status) {
            Status::Passed, Status::Flaky => $this->publish(
                Formatter::testFinished($result->info->name, $duration),
            ),
            Status::Failed, Status::Error => $this->handleFailedTest($result, $duration),
            Status::Skipped => $this->handleSkippedTest($result, $duration),
            Status::Risky => $this->handleRiskyTest($result, $duration),
            Status::Cancelled => $this->handleCancelledTest($result, $duration),
            Status::Aborted => $this->handleAbortedTest($result, $duration),
        };
    }

    /**
     * Handles skipped test status.
     */
    private function handleSkippedTest(TestResult $result, ?int $duration): void
    {
        $this->publish(Formatter::testIgnored($result->info->name));
        $this->publish(Formatter::testFinished($result->info->name, $duration));
    }

    /**
     * Handles cancelled test status.
     */
    private function handleCancelledTest(TestResult $result, ?int $duration): void
    {
        $this->publish(Formatter::testIgnored($result->info->name, 'Test cancelled'));
        $this->publish(Formatter::testFinished($result->info->name, $duration));
    }

    /**
     * Handles failed test status.
     */
    private function handleFailedTest(TestResult $result, ?int $duration): void
    {
        $this->testFailedFromResult($result);
        $this->publish(Formatter::testFinished($result->info->name, $duration));
    }

    /**
     * Handles aborted test status.
     */
    private function handleAbortedTest(TestResult $result, ?int $duration): void
    {
        $this->publish(
            Formatter::testFailed(
                $result->info->name,
                'Test aborted',
                $result->failure !== null ? Helper::formatThrowable($result->failure) : '',
            ),
        );
        $this->publish(Formatter::testFinished($result->info->name, $duration));
    }

    /**
     * Handles risky test status.
     */
    private function handleRiskyTest(TestResult $result, ?int $duration): void
    {
        $this->publish(Formatter::testFinished($result->info->name, $duration));
        $this->publish(
            Formatter::testStdOut(
                $result->info->name,
                'Warning: This test has been marked as risky',
            ),
        );
    }

    /**
     * Publishes a TeamCity service message to stdout.
     *
     * @param non-empty-string $message Formatted TeamCity message
     */
    private function publish(string $message): void
    {
        echo $message . "\n";
    }
}
