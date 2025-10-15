<?php

declare(strict_types=1);

namespace Testo\Interceptor\Internal;

use Testo\Interceptor\Internal\InterceptorMarker as TInterceptor;

/**
 * Processor for interceptors chain.
 *
 * @template-covariant TClass of TInterceptor
 * @template TInput
 * @template-covariant TOutput of mixed
 *
 * @psalm-immutable
 *
 * @internal
 * @psalm-internal Testo\Interceptor
 */
final class Pipeline
{
    /** @var non-empty-string */
    private string $method;

    private \Closure $last;

    /** @var list<TClass> */
    private array $interceptors = [];

    /** @var int<0, max> Current interceptor key */
    private int $current = 0;

    /**
     * @param array<TClass> $interceptors
     */
    private function __construct(
        array $interceptors,
    ) {
        // Reset keys
        $this->interceptors = \array_values($interceptors);
    }

    /**
     * Make sure that interceptors implement the same interface.
     * @template-covariant TInt of TInterceptor
     * @template TIn
     * @template-covariant TOut
     * @param TInt<TIn, TOut> ...$interceptors
     * @return self<TInt, TIn, TOut>
     */
    public static function prepare(TInterceptor ...$interceptors): self
    {
        return new self($interceptors);
    }

    /**
     * @param non-empty-string $method Method name of the all interceptors.
     *
     * @return callable(object): TOutput
     */
    public function with(\Closure $last, string $method): callable
    {
        $new = clone $this;

        $new->last = $last;
        $new->method = $method;

        return $new;
    }

    /**
     * Must be used after {@see self::with()} method.
     *
     * @param TInput $input Input value for the first interceptor.
     *
     * @return TOutput
     */
    public function __invoke(object $input): mixed
    {
        $interceptor = $this->interceptors[$this->current] ?? null;

        if ($interceptor === null) {
            return ($this->last)($input);
        }

        $next = $this->next();

        return $interceptor->{$this->method}($input, $next);
    }

    private function next(): self
    {
        $new = clone $this;
        ++$new->current;

        return $new;
    }
}
