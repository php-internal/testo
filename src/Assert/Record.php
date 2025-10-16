<?php

declare(strict_types=1);

namespace Testo\Assert;

/**
 * Assertion record.
 */
final class Record
{
    public function __construct(
        public readonly bool $passed,
        public readonly string $method,
        public readonly string $message = '',
    ) {}
}
