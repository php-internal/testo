<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Attribute\Interceptable;
use Testo\Interceptor\Internal\InterceptorMarker;

/**
 * Define a fallback interceptor for the {@see Interceptable} attribute.
 *
 * For example:
 *
 * ```
 *  #[\Attribute]
 *  #[FallbackInterceptor(RetryPolicyCallInterceptor::class)]
 *  final class RetryPolicy {}
 * ```
 *
 * Makes sense only for interceptors that are executed during tests execution.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class FallbackInterceptor
{
    public function __construct(
        /**
         * Interceptor class that can handle the attribute.
         *
         * @var class-string<InterceptorMarker>
         */
        public readonly string $class,
    ) {}
}
