<?php

declare(strict_types=1);

namespace Testo\Assert\State;

use Testo\Assert\Support;

/**
 * Assertion exception.
 */
final class AssertException extends \Exception implements Record
{
    /**
     * @param non-empty-string $assertion The assertion result (e.g., "Expected exactly 42, got 43").
     * @param string $context Optional user-provided context describing what is being asserted.
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
     * Failed comparison assertion factory.
     *
     * @param mixed $expected The expected value.
     * @param mixed $actual The actual value to compare against the expected value.
     * @param non-empty-string $message Short description about what exactly is being asserted.
     * @param non-empty-string $pattern The message pattern.
     * @param bool $showDiff Whether to generate a diff between expected and actual values.
     */
    public static function compare(
        mixed $expected,
        mixed $actual,
        string $message,
        string $pattern = 'Expected `%1$s`, got `%2$s`.',
        bool $showDiff = true,
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

    /**
     * Failed `expect exception` assertion factory.
     *
     * @param class-string<\Throwable> $expected The expected exception class.
     * @param \Throwable|null $actual The actual exception thrown, or null if none was thrown.
     */
    public static function exceptionClass(
        string $expected,
        ?\Throwable $actual,
    ): self {
        $msg = $actual === null
            ? "Expected exception of type `$expected`, none thrown."
            : "Expected exception of type `$expected`, got `" . $actual::class . '`.';

        return new self(
            assertion: $msg,
            context: '',
            details: '',
        );
    }

    public function isSuccess(): bool
    {
        return false;
    }

    public function __toString(): string
    {
        return $this->assertion;
    }
}
