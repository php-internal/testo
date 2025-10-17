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
            : StaticState::fail(AssertException::same($expected, $actual, $message));
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
            : StaticState::fail(AssertException::same(null, $actual, $message));
    }

    /**
     * Expects that the test will throw an exception of the given class.
     *
     * @param class-string $class The expected exception class or interface.
     *
     * @note Requires {@see ExpectExceptionInterceptor} to be registered.
     */
    public static function exception(
        string $class,
    ): void {
        StaticState::expectException($class);
    }
}
