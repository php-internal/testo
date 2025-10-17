<?php

declare(strict_types=1);

namespace Testo\Attribute;

use Testo\Assert\Interceptor\ExpectExceptionConfigurator;
use Testo\Interceptor\FallbackInterceptor;

/**
 * Expect exception to be thrown.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
#[FallbackInterceptor(ExpectExceptionConfigurator::class)]
final class ExpectException implements Interceptable
{
    /**
     * @param class-string<\Throwable> $class Expected exception class.
     */
    public function __construct(
        public readonly string $class,
    ) {}
}
