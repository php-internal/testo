<?php

declare(strict_types=1);

namespace Testo\Assert\State;

/**
 * Assertion record.
 */
interface Record
{
    /**
     * Indicates whether the assertion was successful.
     */
    public function isSuccess(): bool;
}
