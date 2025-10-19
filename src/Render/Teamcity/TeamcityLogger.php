<?php

declare(strict_types=1);

namespace Testo\Render\Teamcity;

use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\SuiteInfo;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * TeamCity service messages logger for test reporting.
 *
 * Outputs TeamCity-compatible service messages for CI integration.
 * Messages follow the format: ##teamcity[messageName name='value' attr='value']
 *
 * @link https://www.jetbrains.com/help/teamcity/service-messages.html
 * @internal
 */
final class TeamcityLogger
{
    /**
     * Outputs a test suite started message.
     *
     * @param non-empty-string $name Suite name
     * @param non-empty-string|null $locationHint Location hint for IDE navigation
     */
    public function suiteStarted(string $name, ?string $locationHint = null): void
    {
        $attributes = ['name' => $name];

        $locationHint !== null and $attributes['locationHint'] = $locationHint;

        $this->message('testSuiteStarted', $attributes);
    }

    /**
     * Outputs a test suite finished message.
     *
     * @param non-empty-string $name Suite name
     */
    public function suiteFinished(string $name): void
    {
        $this->message('testSuiteFinished', ['name' => $name]);
    }

    /**
     * Outputs a test suite started message using SuiteInfo.
     */
    public function suiteStartedFromInfo(SuiteInfo $info): void
    {
        $this->suiteStarted($info->name);
    }

    /**
     * Outputs a test suite finished message using SuiteInfo.
     */
    public function suiteFinishedFromInfo(SuiteInfo $info): void
    {
        $this->suiteFinished($info->name);
    }

    /**
     * Outputs a test case started message using CaseInfo.
     *
     * Test case is treated as a suite in TeamCity (a class containing tests).
     */
    public function caseStartedFromInfo(CaseInfo $info): void
    {
        $name = $this->getCaseName($info);
        $locationHint = $this->getCaseLocationHint($info);

        $this->suiteStarted($name, $locationHint);
    }

    /**
     * Outputs a test case finished message using CaseInfo.
     *
     * Test case is treated as a suite in TeamCity (a class containing tests).
     */
    public function caseFinishedFromInfo(CaseInfo $info): void
    {
        $name = $this->getCaseName($info);
        $this->suiteFinished($name);
    }

    /**
     * Closes the test case suite based on test results.
     *
     * Analyzes all test results to determine the overall case status
     * and outputs appropriate TeamCity messages (failed tests, then suite finish).
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
            $this->testStdErr(
                $caseName,
                "Test case failed: {$failedCount} test(s) failed",
            );
        }

        $this->caseFinishedFromInfo($caseInfo);
    }

    /**
     * Outputs a test started message.
     *
     * @param non-empty-string $name Test name
     * @param bool $captureStandardOutput Whether to capture standard output
     * @param non-empty-string|null $locationHint Location hint for IDE navigation
     */
    public function testStarted(string $name, bool $captureStandardOutput = false, ?string $locationHint = null): void
    {
        $attributes = ['name' => $name];

        $captureStandardOutput and $attributes['captureStandardOutput'] = 'true';
        $locationHint !== null and $attributes['locationHint'] = $locationHint;

        $this->message('testStarted', $attributes);
    }

    /**
     * Outputs a test finished message.
     *
     * @param non-empty-string $name Test name
     * @param int<0, max>|null $duration Duration in milliseconds
     */
    public function testFinished(string $name, ?int $duration = null): void
    {
        $attributes = ['name' => $name];

        $duration !== null and $attributes['duration'] = (string) $duration;

        $this->message('testFinished', $attributes);
    }

    /**
     * Outputs a test started message using TestInfo.
     */
    public function testStartedFromInfo(TestInfo $info, bool $captureStandardOutput = false): void
    {
        $locationHint = $this->getTestLocationHint($info);
        $this->testStarted($info->name, $captureStandardOutput, $locationHint);
    }

    /**
     * Outputs a test finished message using TestInfo.
     *
     * @param int<0, max>|null $duration Duration in milliseconds
     */
    public function testFinishedFromInfo(TestInfo $info, ?int $duration = null): void
    {
        $this->testFinished($info->name, $duration);
    }

    /**
     * Outputs a test failed message.
     *
     * @param non-empty-string $name Test name
     * @param non-empty-string $message Failure message
     * @param non-empty-string $details Detailed failure information (stack trace, etc.)
     * @param non-empty-string|null $type Comparison type for diff display (e.g., 'comparisonFailure')
     * @param non-empty-string|null $expected Expected value for diff
     * @param non-empty-string|null $actual Actual value for diff
     */
    public function testFailed(
        string $name,
        string $message,
        string $details = '',
        ?string $type = null,
        ?string $expected = null,
        ?string $actual = null,
    ): void {
        $attributes = [
            'name' => $name,
            'message' => $message,
            'details' => $details,
        ];

        $type !== null and $attributes['type'] = $type;
        $expected !== null and $attributes['expected'] = $expected;
        $actual !== null and $attributes['actual'] = $actual;

        $this->message('testFailed', $attributes);
    }

    /**
     * Outputs a test failed message using TestResult.
     */
    public function testFailedFromResult(TestResult $result): void
    {
        $failure = $result->failure;
        $message = $failure?->getMessage() ?? 'Test failed';
        $details = $failure !== null ? $this->formatThrowable($failure) : '';

        $this->testFailed(
            name: $result->info->name,
            message: $message,
            details: $details,
        );
    }

    /**
     * Outputs a test ignored/skipped message.
     *
     * @param non-empty-string $name Test name
     * @param non-empty-string $message Optional skip reason
     */
    public function testIgnored(string $name, string $message = ''): void
    {
        $attributes = ['name' => $name];

        $message !== '' and $attributes['message'] = $message;

        $this->message('testIgnored', $attributes);
    }

    /**
     * Outputs a test ignored message using TestInfo.
     *
     * @param non-empty-string $message Optional skip reason
     */
    public function testIgnoredFromInfo(TestInfo $info, string $message = ''): void
    {
        $this->testIgnored($info->name, $message);
    }

    /**
     * Outputs a test standard output message.
     *
     * @param non-empty-string $name Test name
     * @param non-empty-string $output Standard output content
     */
    public function testStdOut(string $name, string $output): void
    {
        $this->message('testStdOut', [
            'name' => $name,
            'out' => $output,
        ]);
    }

    /**
     * Outputs a test standard error message.
     *
     * @param non-empty-string $name Test name
     * @param non-empty-string $output Standard error content
     */
    public function testStdErr(string $name, string $output): void
    {
        $this->message('testStdErr', [
            'name' => $name,
            'out' => $output,
        ]);
    }

    /**
     * Outputs a message based on test status.
     */
    public function handleTestResult(TestResult $result, ?int $duration = null): void
    {
        match ($result->status) {
            Status::Passed, Status::Flaky => $this->testFinished($result->info->name, $duration),
            Status::Failed, Status::Error => $this->testFailedFromResult($result),
            Status::Skipped => $this->testIgnored($result->info->name),
            Status::Risky => $this->handleRiskyTest($result, $duration),
            Status::Cancelled => $this->testIgnored($result->info->name, 'Test cancelled'),
            Status::Aborted => $this->testFailed(
                $result->info->name,
                'Test aborted',
                $result->failure !== null ? $this->formatThrowable($result->failure) : '',
            ),
        };
    }

    /**
     * Outputs a progress message.
     *
     * @param non-empty-string $message Progress message
     */
    public function progressMessage(string $message): void
    {
        $this->message('progressMessage', ['text' => $message]);
    }

    /**
     * Outputs a progress start message.
     *
     * @param non-empty-string $message Progress message
     */
    public function progressStart(string $message): void
    {
        $this->message('progressStart', ['text' => $message]);
    }

    /**
     * Outputs a progress finish message.
     *
     * @param non-empty-string $message Progress message
     */
    public function progressFinish(string $message): void
    {
        $this->message('progressFinish', ['text' => $message]);
    }

    /**
     * Outputs a build problem message.
     *
     * @param non-empty-string $description Problem description
     * @param non-empty-string|null $identity Problem identity for deduplication
     */
    public function buildProblem(string $description, ?string $identity = null): void
    {
        $attributes = ['description' => $description];

        $identity !== null and $attributes['identity'] = $identity;

        $this->message('buildProblem', $attributes);
    }

    /**
     * Outputs a build status message.
     *
     * @param non-empty-string $text Status text
     * @param 'FAILURE'|'SUCCESS'|null $status Build status
     */
    public function buildStatus(string $text, ?string $status = null): void
    {
        $attributes = ['text' => $text];

        $status !== null and $attributes['status'] = $status;

        $this->message('buildStatus', $attributes);
    }

    /**
     * Outputs a block opened message for grouping output.
     *
     * @param non-empty-string $name Block name
     */
    public function blockOpened(string $name): void
    {
        $this->message('blockOpened', ['name' => $name]);
    }

    /**
     * Outputs a block closed message.
     *
     * @param non-empty-string $name Block name
     */
    public function blockClosed(string $name): void
    {
        $this->message('blockClosed', ['name' => $name]);
    }

    /**
     * Outputs a compilation started message.
     *
     * @param non-empty-string $compiler Compiler name
     */
    public function compilationStarted(string $compiler): void
    {
        $this->message('compilationStarted', ['compiler' => $compiler]);
    }

    /**
     * Outputs a compilation finished message.
     *
     * @param non-empty-string $compiler Compiler name
     */
    public function compilationFinished(string $compiler): void
    {
        $this->message('compilationFinished', ['compiler' => $compiler]);
    }

    /**
     * Handles risky test status.
     */
    private function handleRiskyTest(TestResult $result, ?int $duration): void
    {
        $this->testFinished($result->info->name, $duration);
        $this->testStdOut(
            $result->info->name,
            'Warning: This test has been marked as risky',
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
     * Outputs a TeamCity service message.
     *
     * @param non-empty-string $messageName Message type name
     * @param array<non-empty-string, string> $attributes Message attributes
     */
    private function message(string $messageName, array $attributes): void
    {
        $formattedAttributes = $this->formatAttributes($attributes);
        echo "##teamcity[{$messageName}{$formattedAttributes}]\n";
    }

    /**
     * Formats message attributes.
     *
     * @param array<non-empty-string, string> $attributes
     * @return non-empty-string Formatted attributes string
     */
    private function formatAttributes(array $attributes): string
    {
        if ($attributes === []) {
            return '';
        }

        $parts = [];
        foreach ($attributes as $key => $value) {
            $escapedValue = $this->escape($value);
            $parts[] = " {$key}='{$escapedValue}'";
        }

        return \implode('', $parts);
    }

    /**
     * Escapes a value for TeamCity service messages.
     *
     * Special characters that need escaping:
     * - ' (apostrophe) -> |'
     * - \n (newline) -> |n
     * - \r (carriage return) -> |r
     * - | (pipe) -> ||
     * - [ (opening bracket) -> |[
     * - ] (closing bracket) -> |]
     * - Unicode characters 0x0000-0x001F -> |0x<code>
     */
    private function escape(string $value): string
    {
        return \str_replace(
            ["|", "'", "\n", "\r", "[", "]"],
            ["||", "|'", "|n", "|r", "|[", "|]"],
            $value,
        );
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
}
