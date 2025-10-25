<?php

declare(strict_types=1);

namespace Testo;

use Testo\Assert\Interceptor\ExpectExceptionInterceptor;
use Testo\Assert\State\AssertException;
use Testo\Assert\StaticState;
use Testo\Assert\Support;

/**
 * Assertion utilities.
 */
final class Assert
{
    /**
     * Asserts that two values are the same (identical).
     *
     * @param mixed $expected The expected value.
     * @param mixed $actual The actual value to compare against the expected value.
     * @param string $message Short description about what exactly is being asserted.
     * @throws AssertException when the assertion fails.
     */
    public static function same(mixed $expected, mixed $actual, string $message = ''): void
    {
        $actual === $expected
            ? StaticState::log('Assert same: `' . Support::stringify($expected) . '`', $message)
            : StaticState::fail(AssertException::compare($expected, $actual, $message));
    }

    /**
     * Asserts that two values are the not same (not identical).
     *
     * @param mixed $expected The expected value.
     * @param mixed $actual The actual value to compare against the expected value.
     * @param string $message Short description about what exactly is being asserted.
     * @throws AssertException when the assertion fails.
     */
    public static function notSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        $actual !== $expected
            ? StaticState::log('Assert not same: `' . Support::stringify($expected) . '`', $message)
            : StaticState::fail(AssertException::compare(
                $expected,
                $actual,
                $message,
                pattern: 'Failed asserting that `%s` is not identical to `%s`.',
                showDiff: false,
            ));
    }

    /**
     * Asserts that the condition is true.
     *
     * @param bool $condition The condition asserting to be true.
     * @param string $message Short description about what exactly is being asserted.
     * @throws AssertException when the assertion fails.
     */
    public static function true(bool $condition, string $message = ''): void
    {
        $condition === true
            ? StaticState::log('Assert true', $message)
            : StaticState::fail(AssertException::compare(
                true,
                $condition,
                $message,
                'Failed asserting that value `%2$s` is `%1$s`.',
            ));
    }

    /**
     * Asserts that the condition is false.
     *
     * @param bool $condition The condition asserting to be false.
     * @param string $message Short description about what exactly is being asserted.
     * @throws AssertException when the assertion fails.
     */
    public static function false(bool $condition, string $message = ''): void
    {
        $condition === false
            ? StaticState::log('Assert false', $message)
            : StaticState::fail(AssertException::compare(
                false,
                $condition,
                $message,
                'Failed asserting that value `%2$s` is `%1$s`.',
            ));
    }

    /**
     * Asserts that the given value is null.
     *
     * @param mixed $actual The actual value to check for null.
     * @param string $message Short description about what exactly is being asserted.
     * @throws AssertException when the assertion fails.
     */
    public static function null(
        mixed $actual,
        string $message = '',
    ): void {
        $actual === null
            ? StaticState::log('Assert null', $message)
            : StaticState::fail(AssertException::compare(null, $actual, $message));
    }

    /**
     * Expects that the test will throw an exception of the given class.
     *
     * @param class-string|\Throwable $classOrObject The expected exception class, interface, or an exception object.
     *
     * @note Requires {@see ExpectExceptionInterceptor} to be registered.
     */
    public static function exception(
        string|\Throwable $classOrObject,
    ): void {
        StaticState::expectException($classOrObject);
    }

    /**
     * Asserts that the given objects do not leak memory after the test execution.
     *
     * @param object ...$objects The objects to monitor for memory leaks.
     */
    public static function leaks(object ...$objects): void
    {
        StaticState::trackObjects(...$objects);
    }
}
