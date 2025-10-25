<?php

declare(strict_types=1);

namespace Testo\Assert\State;

/**
 * Assertion record.
 */
final class Success implements Record
{
    /**
     * @param non-empty-string $assertion The assertion result (e.g., "Same: 42", "Assert `true`").
     * @param string $context Optional user-provided context describing what is being asserted.
     */
    public function __construct(
        public readonly string $assertion,
        public readonly string $context = '',
        private readonly bool $success = true,
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function __toString(): string
    {
        return $this->assertion;
    }
}
