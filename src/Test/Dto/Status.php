<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

enum Status
{
    /**
     * Test executed successfully with all assertions passing.
     */
    case Passed;

    /**
     * Test failed due to assertion failure.
     */
    case Failed;

    /**
     * Test was skipped and not executed.
     */
    case Skipped;

    /**
     * Test encountered an error or exception during execution.
     */
    case Error;

    /**
     * Test ran but has potential issues (no assertions, output, or global state changes).
     */
    case Risky;

    /**
     * Completed but failed at least once.
     */
    case Flaky;

    /**
     * Test was cancelled before completion.
     */
    case Cancelled;
}
