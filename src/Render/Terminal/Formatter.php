<?php

declare(strict_types=1);

namespace Testo\Render\Terminal;

use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\SuiteResult;

/**
 * Formats terminal output messages with support for different output formats.
 *
 * @internal
 */
final class Formatter
{
    private function __construct() {}

    /**
     * Formats the header for starting test run.
     *
     * @return non-empty-string
     */
    public static function runHeader(): string
    {
        return Style::bold(' Running Tests') . "\n\n";
    }

    /**
     * Formats a suite header.
     *
     * @param non-empty-string $name
     * @return non-empty-string
     */
    public static function suiteHeader(string $name, OutputFormat $format): string
    {
        return match ($format) {
            OutputFormat::Verbose => "\n " . Style::bold("Suite: {$name}") . "\n",
            OutputFormat::Compact => "\n" . Style::bold("Suite: {$name}") . "\n",
            OutputFormat::Dots => "\n" . Style::bold("Suite: {$name}") . "\n",
        };
    }

    /**
     * Formats a test case header.
     *
     * @param non-empty-string $name
     * @return non-empty-string
     */
    public static function caseHeader(string $name, OutputFormat $format): string
    {
        return match ($format) {
            OutputFormat::Verbose => "\n   " . Style::bold("Case: {$name}") . "\n",
            OutputFormat::Compact => " " . Style::bold($name) . "\n",
            OutputFormat::Dots => " " . Style::bold($name) . " ",
        };
    }

    /**
     * Formats a single test line.
     *
     * @param non-empty-string $name
     * @param int<0, max>|null $duration Duration in milliseconds
     * @return non-empty-string
     */
    public static function testLine(
        string $name,
        Status $status,
        ?int $duration,
        OutputFormat $format,
    ): string {
        return match ($format) {
            OutputFormat::Compact, OutputFormat::Verbose => self::formatCompactTest($name, $status, $duration, $format),
            OutputFormat::Dots => self::formatDotTest($status),
        };
    }

    /**
     * Formats case footer (dots mode).
     */
    public static function caseFooter(OutputFormat $format): string
    {
        return $format === OutputFormat::Dots ? "\n" : '';
    }

    /**
     * Formats case summary (verbose mode only).
     */
    public static function caseSummary(CaseResult $result, OutputFormat $format): string
    {
        if ($format !== OutputFormat::Verbose) {
            return '';
        }

        $parts = [];
        $passed = $result->countPassedTests();
        $failed = $result->countFailedTests();
        $skipped = $result->countTests(Status::Skipped);
        $risky = $result->countTests(Status::Risky);
        $cancelled = $result->countTests(Status::Cancelled);
        $flaky = $result->countTests(Status::Flaky);

        $passed > 0 and $parts[] = Style::success("{$passed} passed");
        $failed > 0 and $parts[] = Style::error("{$failed} failed");
        $skipped > 0 and $parts[] = Style::warning("{$skipped} skipped");
        $risky > 0 and $parts[] = Style::warning("{$risky} risky");
        $cancelled > 0 and $parts[] = Style::dim("{$cancelled} cancelled");
        $flaky > 0 and $parts[] = Style::info("{$flaky} flaky");

        $parts === [] and $parts[] = 'no tests';

        $summary = \implode(', ', $parts);
        return "   " . Style::dim("Summary: {$summary}") . "\n";
    }

    /**
     * Formats suite summary.
     */
    public static function suiteSummary(SuiteResult $result, OutputFormat $format): string
    {
        $parts = [];
        $passed = $result->countPassedTests();
        $failed = $result->countFailedTests();
        $skipped = $result->countTests(Status::Skipped);
        $risky = $result->countTests(Status::Risky);
        $cancelled = $result->countTests(Status::Cancelled);
        $flaky = $result->countTests(Status::Flaky);
        $error = $result->countTests(Status::Error);
        $aborted = $result->countTests(Status::Aborted);

        $passed > 0 and $parts[] = Style::success("{$passed} passed");
        $failed > 0 and $parts[] = Style::error("{$failed} failed");
        $error > 0 and $parts[] = Style::error("{$error} error");
        $skipped > 0 and $parts[] = Style::warning("{$skipped} skipped");
        $risky > 0 and $parts[] = Style::warning("{$risky} risky");
        $cancelled > 0 and $parts[] = Style::dim("{$cancelled} cancelled");
        $flaky > 0 and $parts[] = Style::info("{$flaky} flaky");
        $aborted > 0 and $parts[] = Style::error("{$aborted} aborted");

        if ($parts === []) {
            return '';
        }

        $summary = \implode(', ', $parts);
        $prefix = $format === OutputFormat::Verbose ? ' ' : '';

        return "{$prefix}" . Style::dim("Suite: {$summary}") . "\n";
    }

    /**
     * Formats progress indicator.
     *
     * @param int<0, max> $current
     * @param int<0, max> $total
     * @return non-empty-string
     */
    public static function progress(int $current, int $total): string
    {
        return "\n " . Style::dim("Progress: {$current}/{$total} tests completed") . "\n";
    }

    /**
     * Formats failures section header.
     *
     * @return non-empty-string
     */
    public static function failuresHeader(): string
    {
        return "\n\n " . Style::bold(Style::error('Failures:')) . "\n";
    }

    /**
     * Formats a single failure detail.
     *
     * @param int<1, max> $index
     * @param non-empty-string $testName
     * @param non-empty-string $message
     * @param non-empty-string $details
     * @param int<0, max>|null $duration
     * @return non-empty-string
     */
    public static function failureDetail(
        int $index,
        string $testName,
        string $message,
        string $details,
        ?int $duration,
    ): string {
        $durationStr = $duration !== null
            ? Style::dim(" ({$duration}ms)")
            : '';

        $header = "\n " . Style::bold("{$index}) {$testName}") . $durationStr . "\n";
        $messageBlock = "\n    {$message}\n";
        $detailsBlock = $details !== '' ? "\n" . self::indentText($details, '    ') . "\n" : '';

        return $header . $messageBlock . $detailsBlock;
    }

    /**
     * Formats final summary section.
     *
     * @param int<0, max> $total
     * @param int<0, max> $passed
     * @param int<0, max> $failed
     * @param int<0, max> $skipped
     * @param int<0, max> $risky
     * @param float $duration Duration in seconds
     * @return non-empty-string
     */
    public static function summary(
        int $total,
        int $passed,
        int $failed,
        int $skipped,
        int $risky,
        float $duration,
    ): string {
        $parts = [];
        $passed > 0 and $parts[] = Style::success("{$passed} passed");
        $failed > 0 and $parts[] = Style::error("{$failed} failed");
        $skipped > 0 and $parts[] = Style::warning("{$skipped} skipped");
        $risky > 0 and $parts[] = Style::warning("{$risky} risky");

        $testsLine = \implode(', ', $parts);
        $durationFormatted = \number_format($duration, 2);

        $summary = "\n\n " . Style::bold('Summary') . "\n\n";
        $summary .= " Tests:    {$testsLine} ({$total} total)\n";
        $summary .= " Duration: {$durationFormatted}s\n";

        return $summary;
    }

    /**
     * Formats final status banner.
     *
     * @return non-empty-string
     */
    public static function finalBanner(bool $success): string
    {
        $bg = $success ? Color::BgGreen : Color::BgRed;
        $text = $success ? 'PASSED' : 'FAILED';

        return "\n " . Style::banner($text, $bg) . "\n";
    }

    /**
     * Formats assertion history header.
     *
     * @return non-empty-string
     */
    public static function assertionHistoryHeader(OutputFormat $format): string
    {
        if ($format === OutputFormat::Dots) {
            return '';
        }

        $indent = $format === OutputFormat::Verbose ? '       ' : '     ';
        return $indent . Style::dim('Assertion history:') . "\n";
    }

    /**
     * Formats a single assertion line.
     *
     * @param \Testo\Assert\State\Record $assertion
     * @param OutputFormat $format
     * @return non-empty-string
     */
    public static function assertionLine(object $assertion, OutputFormat $format): string
    {
        if ($format === OutputFormat::Dots) {
            return '';
        }

        $indent = $format === OutputFormat::Verbose ? '       ' : '     ';
        $symbol = $assertion->isSuccess()
            ? Style::success('✓')
            : Style::error('✗');

        $text = (string) $assertion;

        return "{$indent}  {$symbol} {$text}\n";
    }

    /**
     * Formats test in compact/verbose mode.
     *
     * @param non-empty-string $name
     * @param int<0, max>|null $duration
     */
    private static function formatCompactTest(
        string $name,
        Status $status,
        ?int $duration,
        OutputFormat $format,
    ): string {
        $symbol = self::getStatusSymbol($status);
        $indent = $format === OutputFormat::Verbose ? '     ' : '   ';

        $durationStr = $duration !== null
            ? Style::dim(" ({$duration}ms)")
            : '';

        return "{$indent}{$symbol} {$name}{$durationStr}\n";
    }

    /**
     * Formats test in dots mode.
     */
    private static function formatDotTest(Status $status): string
    {
        $symbol = match ($status) {
            Status::Passed => DotSymbol::Passed->value,
            Status::Failed => Style::error(DotSymbol::Failed->value),
            Status::Skipped => Style::warning(DotSymbol::Skipped->value),
            Status::Error, Status::Aborted => Style::error(DotSymbol::Error->value),
            Status::Risky => Style::warning(DotSymbol::Risky->value),
            Status::Flaky => Style::info(DotSymbol::Passed->value),
            Status::Cancelled => Style::dim(DotSymbol::Skipped->value),
        };

        return $symbol;
    }

    /**
     * Gets colored status symbol.
     */
    private static function getStatusSymbol(Status $status): string
    {
        return match ($status) {
            Status::Passed => Style::success(Symbol::Success->value),
            Status::Failed => Style::error(Symbol::Failure->value),
            Status::Skipped => Style::warning(Symbol::Skipped->value),
            Status::Error, Status::Aborted => Style::error(Symbol::Error->value),
            Status::Risky => Style::warning(Symbol::Risky->value),
            Status::Flaky => Style::info(Symbol::Flaky->value),
            Status::Cancelled => Style::dim(Symbol::Skipped->value),
        };
    }

    /**
     * Indents each line of text.
     *
     * @param non-empty-string $text
     * @param non-empty-string $indent
     */
    private static function indentText(string $text, string $indent): string
    {
        $lines = \explode("\n", $text);
        $indentedLines = \array_map(
            static fn(string $line): string => $line !== '' ? $indent . $line : '',
            $lines,
        );

        return \implode("\n", $indentedLines);
    }
}
