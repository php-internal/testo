<?php

declare(strict_types=1);

namespace Testo\Attribute;

/**
 * Retry test on failure.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class RetryPolicy implements Interceptable
{
    public function __construct(
        public readonly int $maxAttempts = 3,
    ) {}
}
