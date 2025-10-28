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
                pattern: 'Failed asserting that `%s` is not identical to `%s`',
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
                'Failed asserting that value `%2$s` is `%1$s`',
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
                'Failed asserting that value `%2$s` is `%1$s`',
            ));
    }

    /**
     * Asserts that the actual object is an instance of the expected class/interface.
     *
     * @param string $expected Expected class/interface (class-string).
     * @param mixed $actual The actual value to compare against the expected value.
     * @param string $message Short description about what exactly is being asserted.
     * @throws AssertException when the assertion fails.
     */
    public static function instanceOf(string $expected, mixed $actual, string $message = ''): void
    {
        $actual instanceof $expected
            ? StaticState::log('Assert instance of `' . $expected . '`', $message)
            : StaticState::fail(AssertException::compare(
                $expected,
                $actual,
                $message,
                'Expected instance of `%2$s`, got `%1$s`',
            ));
    }

    /**
     * Asserts that given collection contains expected value.
     *
     * @param mixed $needle The expected value.
     * @param iterable $haystack Iterable (array or Traversable) to search in.
     * @param string $message Short description about what exactly is being asserted.
     * @throws AssertException when the assertion fails.
     */
    public static function contains(mixed $needle, iterable $haystack, string $message = ''): void
    {
        foreach ($haystack as $element) {
            if ($needle === $element) {
                StaticState::log('Assert contains', $message);
                return;
            }
        }
        StaticState::fail(AssertException::compare(
            $needle,
            $haystack,
            $message,
            'Failed asserting that `%1$s` contains `%2$s`',
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
            ? StaticState::log('Assert `null`', $message)
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
     * Throws an exception if the condition is true.
     *
     * @param bool $condition The condition to check.
     * @param \Throwable|class-string<\Throwable> $exception The exception to throw or exception class.
     * @param string $message Short description about what exactly is being asserted.
     * @throws \Throwable when the condition is true.
     */
    public static function throwsIf(bool $condition, \Throwable|string $exception, string $message = ''): void
    {
        if ($condition === true) {
            self::exception($exception);
            throw \is_string($exception) ? new $exception($message) : $exception;
        }

        StaticState::log('Assert throws if: condition is false', $message);
    }

    /**
     * Throws an exception unless the condition is true.
     *
     * @param bool $condition The condition to check.
     * @param \Throwable|class-string<\Throwable> $exception The exception to throw or exception class.
     * @param string $message Short description about what exactly is being asserted.
     * @throws \Throwable when the condition is false.
     */
    public static function throwsUnless(bool $condition, \Throwable|string $exception, string $message = ''): void
    {
        if ($condition === false) {
            self::exception($exception);
            throw \is_string($exception) ? new $exception($message) : $exception;
        }

        StaticState::log('Assert throws unless: condition is true', $message);
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
