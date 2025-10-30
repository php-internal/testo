<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Assert\State\Record;
use Testo\Test\Dto\TestResult;

/**
 * Collects assertions.
 */
final class TestState
{
    /**
     * @var list<Record> The history of assertions.
     */
    public array $history = [];

    /**
     * @note that the expectation list will be processed in LIFO order.
     *
     * @var list<callable(TestResult, TestState): TestResult> List of expectation handlers.
     */
    public array $expectations = [];
}
