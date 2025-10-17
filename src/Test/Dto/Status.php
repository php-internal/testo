<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

use Testo\Attribute\RetryPolicy;
use Testo\Assert;

/**
 * Possible statuses of a test execution.
 */
enum Status
{
    /**
     * Test executed successfully and all ASSERTIONS PASSED without other issues.
     *
     * @see Assert
     */
    case Passed;

    /**
     * Test failed due to ASSERTION or an EXPECTATION failure.
     *
     * @see Assert
     */
    case Failed;

    /**
     * Test was SKIPPED and not executed.
     */
    case Skipped;

    /**
     * Test encountered an EXCEPTION during execution.
     */
    case Error;

    /**
     * Test COMPLETED successfully but has potential ISSUES (no assertions, output, or global state changes).
     */
    case Risky;

    /**
     * COMPLETED but after RETRIES due to intermittent failures.
     *
     * @see RetryPolicy
     */
    case Flaky;

    /**
     * Test was CANCELLED before completion.
     */
    case Cancelled;

    /**
     * Successfully or not, the test has reached a terminal state.
     */
    public function isCompleted(): bool
    {
        return match ($this) {
            self::Cancelled,
            self::Skipped => false,
            default => true,
        };
    }

    /**
     * Indicates whether the test execution is considered successful.
     */
    public function isSuccessful(): bool
    {
        return match ($this) {
            self::Passed,
            self::Flaky => true,
            default => false,
        };
    }

    /**
     * Indicates whether the test execution is considered a failure.
     */
    public function isFailure(): bool
    {
        return match ($this) {
            self::Failed,
            self::Error => true,
            default => false,
        };
    }
}
