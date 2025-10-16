<?php

declare(strict_types=1);

namespace Testo\Assert;

final class Assert
{
    public static function same(mixed $expect, mixed $value, string $message = ''): void
    {
        if ($value !== $expect) {
            StaticState::fail(__FUNCTION__);
            throw new \RuntimeException(
                \sprintf(
                    "Expected %s, got %s%s",
                    self::stringify($expect),
                    self::stringify($value),
                    $message === '' ? '' : ': ' . $message,
                ),
            );
        }

        StaticState::pass(__FUNCTION__);
    }

    /**
     * Convert a value to a string for error messages.
     */
    protected static function stringify(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            $value === true => 'true',
            $value === false => 'false',
            \is_string($value) => '"' . \str_replace('"', '\\"', $value) . '"',
            \is_array($value) => 'array(' . \count($value) . ')',
            \is_resource($value) => 'resource',
            \is_object($value) => $value::class,
            default => (string) $value,
        };
    }
}
