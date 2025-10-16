<?php

declare(strict_types=1);

namespace Testo\Assert;

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

    public static function pass(string $name): void
    {
        self::$collector === null or self::$collector->history[] = new Record(
            passed: true,
            method: $name,
        );
    }

    public static function fail(string $name): void
    {
        self::$collector === null or self::$collector->history[] = new Record(
            passed: false,
            method: $name,
        );
    }
}
