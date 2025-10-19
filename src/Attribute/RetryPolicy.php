<?php

declare(strict_types=1);

namespace Testo\Attribute;

use Testo\Interceptor\TestCallInterceptor\RetryPolicyRunInterceptor;
use Testo\Module\Interceptor\FallbackInterceptor;

/**
 * Retry test on failure.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::TARGET_CLASS )]
#[FallbackInterceptor(RetryPolicyRunInterceptor::class)]
final class RetryPolicy implements Interceptable
{
    public function __construct(
        /**
         * Maximum number of attempts.
         */
        public readonly int $maxAttempts = 3,

        /**
         * Mark the test as flaky if it passed on retry.
         */
        public readonly bool $markFlaky = true,
    ) {}
}
