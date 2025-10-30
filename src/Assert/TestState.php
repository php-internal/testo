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

    public ?\Throwable $failure = null;

    /**
     * @var list<callable(TestResult, TestState): TestResult> List of expectation handlers.
     */
    public array $expectations = [];
}
