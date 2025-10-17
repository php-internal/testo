<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Assert\Cases\AssertException;
use Testo\Assert\Cases\Success;

/**
 * Holds the current assertion collector.
 *
 * @internal
 * @psalm-internal Testo\Assert
 */
final class StaticState
{
    public static ?AssertCollector $collector = null;

    /**
     * Swaps the current collector with the given one.
     *
     * @return AssertCollector|null The previous collector.
     */
    public static function swap(?AssertCollector $collector): ?AssertCollector
    {
        [self::$collector, $collector] = [$collector, self::$collector];
        return $collector;
    }

    /**
     * @param non-empty-string $assertion The assertion result (e.g., "Same: 42", "Assert `true`").
     * @param non-empty-string $context Optional user-provided context describing what is being asserted.
     */
    public static function log(string $assertion, string $context): void
    {
        self::$collector === null or self::$collector->history[] = new Success(
            assertion: $assertion,
            context: $context,
        );
    }

    /**
     * Log a failed assertion and throw the given exception.
     *
     * @template T of AssertException
     * @param T $failure The assertion failure.
     * @throws T
     */
    public static function fail(AssertException $failure): never
    {
        self::$collector === null or self::$collector->history[] = $failure;
        throw $failure;
    }
}
