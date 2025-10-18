<?php

declare(strict_types=1);

namespace Testo\Assert\State;

/**
 * Expected exception declaration.
 */
final class ExpectedException
{
    /**
     * @param class-string|\Throwable $classOrObject Expected exception class, interface, or an object.
     */
    public function __construct(
        public readonly string|\Throwable $classOrObject,
    ) {}
}
