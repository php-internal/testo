<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Assert\Expectation\ExpectedException;
use Testo\Assert\State\AssertException;
use Testo\Assert\State\Success;

/**
 * Holds the current assertion collector.
 *
 * @internal
 * @psalm-internal Testo\Assert
 */
final class StaticState
{
    public static ?TestState $state = null;

    /**
     * Swap the current collector with the given one.
     *
     * @return TestState|null The previous collector.
     */
    public static function swap(?TestState $collector): ?TestState
    {
        [self::$state, $collector] = [$collector, self::$state];
        return $collector;
    }

    /**
     * Get the current collector.
     */
    public static function current(): ?TestState
    {
        return self::$state;
    }

    /**
     * @param non-empty-string $assertion The assertion result (e.g., "Same: 42", "Assert `true`").
     * @param non-empty-string $context Optional user-provided context describing what is being asserted.
     */
    public static function log(string $assertion, string $context): void
    {
        self::$state === null or self::$state->history[] = new Success(
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
        self::$state === null or self::$state->history[] = $failure;
        throw $failure;
    }

    /**
     * Set the expected exception for the current test.
     *
     * @param class-string|\Throwable $classOrObject The expected exception class, interface, or an exception object.
     *
     * @throws \RuntimeException when there is no current {@see TestState}.
     */
    public static function expectException(
        string|\Throwable $classOrObject,
    ): void {
        # todo make the exception friendlier
        self::$state === null and throw new \RuntimeException(
            'No current AssertState to set expected exception on.',
        );
        self::$state->expectations[] = new ExpectedException($classOrObject);
    }

    /**
     * Track the given objects in the current test state to detect memory leaks.
     *
     * @param object ...$objects The objects to track.
     */
    public static function trackObjects(
        object ...$objects,
    ): void {
        foreach ($objects as $k => $object) {
            $name = \is_string($k) ? $k : true;
            self::$state->weakMap->offsetSet($object, $name);
        }
    }
}
