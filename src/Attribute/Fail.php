<?php

declare(strict_types=1);

namespace Testo\Attribute;

use Testo\Assert\Interceptor\FailConfigurator;
use Testo\Module\Interceptor\FallbackInterceptor;

/**
 * Expect the test to fail with a specific message.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
#[FallbackInterceptor(FailConfigurator::class)]
final class Fail implements Interceptable
{
    /**
     * @param string|null $message Expected failure message (null = any failure is acceptable).
     */
    public function __construct(
        public readonly ?string $message = null,
    ) {}
}