<?php

declare(strict_types=1);

namespace Testo\Assert\State;

/**
 * Assertion record.
 */
interface Record extends \Stringable
{
    /**
     * Indicates whether the assertion was successful.
     */
    public function isSuccess(): bool;

    /**
     * Returns user-provided message for the assertion.
     *
     * @return non-empty-string|null
     */
    public function getContext(): ?string;
}
