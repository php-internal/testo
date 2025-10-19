<?php

declare(strict_types=1);

namespace Testo\Render\Teamcity;

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
     * Analyzes all case results to determine the overall suite status
     * and publishes appropriate TeamCity messages.
     */
    public function handleSuiteResult(SuiteInfo $info, SuiteResult $result): void
    {
        $hasFailures = false;

        foreach ($result as $caseResult) {
            foreach ($caseResult as $testResult) {
                if ($testResult->status->isFailure()) {
                    $hasFailures = true;
                    break 2;
                }
            }
        }

        // Report suite-level failure if any tests failed
        if ($hasFailures) {
            $failedCount = $this->countFailedTestsInSuite($result);
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
        $name = $this->getCaseName($info);
        $locationHint = $this->getCaseLocationHint($info);

        $this->publish(Formatter::suiteStarted($name, $locationHint));
    }

    /**
     * Publishes test case finished message using CaseInfo.
     *
     * Test case is treated as a suite in TeamCity (a class containing tests).
     */
    public function caseFinishedFromInfo(CaseInfo $info): void
    {
        $name = $this->getCaseName($info);
        $this->publish(Formatter::suiteFinished($name));
    }

    /**
     * Handles test case result.
     *
     * Analyzes all test results to determine the overall case status
     * and publishes appropriate TeamCity messages.
     *
     * @param int<0, max>|null $duration Duration in milliseconds for the case
     */
    public function handleCaseResult(CaseInfo $caseInfo, CaseResult $result, ?int $duration = null): void
    {
        $hasFailures = false;

        foreach ($result as $testResult) {
            if ($testResult->status->isFailure()) {
                $hasFailures = true;
                break;
            }
        }

        // Report case-level failure if any tests failed
        if ($hasFailures) {
            $caseName = $this->getCaseName($caseInfo);
            $failedCount = $this->countFailedTests($result);
            $this->publish(
                Formatter::testStdErr(
                    $caseName,
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
        $locationHint = $this->getTestLocationHint($info);
        $this->publish(Formatter::testStarted($info->name, $captureStandardOutput, $locationHint));
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
        $details = $failure !== null ? $this->formatThrowable($failure) : '';

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
            Status::Failed, Status::Error => $this->testFailedFromResult($result),
            Status::Skipped => $this->publish(Formatter::testIgnored($result->info->name)),
            Status::Risky => $this->handleRiskyTest($result, $duration),
            Status::Cancelled => $this->publish(
                Formatter::testIgnored($result->info->name, 'Test cancelled'),
            ),
            Status::Aborted => $this->publish(
                Formatter::testFailed(
                    $result->info->name,
                    'Test aborted',
                    $result->failure !== null ? $this->formatThrowable($result->failure) : '',
                ),
            ),
        };
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
     * Gets the name of a test case for TeamCity output.
     *
     * @return non-empty-string
     */
    private function getCaseName(CaseInfo $info): string
    {
        $reflection = $info->definition->reflection;

        return $reflection !== null
            ? $reflection->getShortName()
            : 'UnknownTestCase';
    }

    /**
     * Counts the number of failed tests in a CaseResult.
     *
     * @return int<0, max>
     */
    private function countFailedTests(CaseResult $result): int
    {
        $count = 0;

        foreach ($result as $testResult) {
            $testResult->status->isFailure() and $count++;
        }

        return $count;
    }

    /**
     * Counts the number of failed tests in a SuiteResult.
     *
     * @return int<0, max>
     */
    private function countFailedTestsInSuite(SuiteResult $result): int
    {
        $count = 0;

        foreach ($result as $caseResult) {
            foreach ($caseResult as $testResult) {
                $testResult->status->isFailure() and $count++;
            }
        }

        return $count;
    }

    /**
     * Formats a throwable into a detailed string.
     */
    private function formatThrowable(\Throwable $throwable): string
    {
        $class = $throwable::class;
        $message = $throwable->getMessage();
        $file = $throwable->getFile();
        $line = $throwable->getLine();
        $trace = $throwable->getTraceAsString();

        return "{$class}: {$message}\nFile: {$file}:{$line}\n\nStack trace:\n{$trace}";
    }

    /**
     * Gets location hint for a test case.
     *
     * Format: php_qn://path/to/file.php::\ClassName
     */
    private function getCaseLocationHint(CaseInfo $info): ?string
    {
        $reflection = $info->definition->reflection;

        if ($reflection === null) {
            return null;
        }

        $file = $reflection->getFileName();
        $className = $reflection->getName();

        return $file !== false
            ? \sprintf('php_qn://%s::\\%s', $file, $className)
            : null;
    }

    /**
     * Gets location hint for a test method.
     *
     * Format: php_qn://path/to/file.php::\ClassName::methodName
     */
    private function getTestLocationHint(TestInfo $info): ?string
    {
        $reflection = $info->testDefinition->reflection;
        $caseReflection = $info->caseInfo->definition->reflection;

        if ($caseReflection === null) {
            return null;
        }

        $file = $reflection->getFileName();
        $className = $caseReflection->getName();
        $methodName = $reflection->getName();

        return $file !== false
            ? \sprintf('php_qn://%s::\\%s::%s', $file, $className, $methodName)
            : null;
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
