<?php

declare(strict_types=1);

namespace Testo\Assert\State;

/**
 * Expected failure declaration.
 */
final class ExpectedFailure
{
    /**
     * @param string|null $message Expected failure message (null = any failure is acceptable).
     */
    public function __construct(
        public readonly ?string $message,
    ) {}
}