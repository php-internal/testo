<?php

declare(strict_types=1);

namespace Testo\Assert\State;

/**
 * Expected exception declaration.
 */
final class ExpectedException
{
    /**
     * @param class-string $class Expected exception class.
     */
    public function __construct(
        public readonly string $class,
    ) {}
}
