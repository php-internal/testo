<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Assert\Cases\AssertException;

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
            ? StaticState::log(
                assertion: 'Assert same: `' . Support::stringify($expected) . '`',
                context: $message,
            )
            : StaticState::fail(AssertException::same($expected, $actual, $message));
    }
}
