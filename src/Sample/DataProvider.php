<?php

declare(strict_types=1);

namespace Testo\Sample;

use Testo\Attribute\Interceptable;
use Testo\Sample\Internal\DataProviderInterceptor;
use Testo\Module\Interceptor\FallbackInterceptor;

/**
 * Attribute to specify a data provider for the test.
 *
 * The data provider should be a callable that returns an iterable of argument sets.
 * Each argument set will be used to invoke the test method separately.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE)]
#[FallbackInterceptor(DataProviderInterceptor::class)]
final class DataProvider implements Interceptable
{
    public readonly \Closure $provider;

    public function __construct(
        callable $provider,
    ) {
        $this->provider = $provider(...);
    }
}
