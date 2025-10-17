<?php

declare(strict_types=1);

namespace Testo\Assert\Cases;

use Testo\Assert\Record;
use Testo\Assert\Support;

/**
 * Assertion exception.
 */
final class AssertException extends \Exception implements Record
{
    /**
     * @param non-empty-string $assertion The assertion result (e.g., "Expected exactly 42, got 43").
     * @param non-empty-string $context Optional user-provided context describing what is being asserted.
     * @param string $details The detailed assertion failure information (diff).
     */
    final protected function __construct(
        public readonly string $assertion,
        public readonly string $context,
        public readonly string $details,
    ) {
        parent::__construct();
    }

    /**
     * Failed `same` assertion factory.
     * @param mixed $expected The expected value.
     * @param mixed $actual The actual value to compare against the expected value.
     * @param non-empty-string $message Short description about what exactly is being asserted.
     * @param non-empty-string $pattern The message pattern.
     */
    public static function same(
        mixed $expected,
        mixed $actual,
        string $message,
        string $pattern = 'Expected `%1$s`, got `%2$s.`',
    ): self {
        # todo
        $diff = '';

        $msg = \sprintf(
            $pattern,
            Support::stringify($actual),
            Support::stringify($expected),
        );
        return new self(
            assertion: $msg,
            context: $message,
            details: $diff,
        );
    }

    public function isSuccess(): bool
    {
        return false;
    }
}
