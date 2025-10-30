<?php

declare(strict_types=1);

namespace Testo\Sample;

use Testo\Test\Dto\TestResult;

/**
 * Aggregate result for multiple test runs.
 *
 * @api
 */
final class MultipleResult
{
    public function __construct(
        /**
         * @var non-empty-array<array-key, TestResult>
         */
        public readonly array $results,
    ) {}
}
