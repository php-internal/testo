<?php

declare(strict_types=1);

namespace Testo\Assert;

class AssertionFailed extends \InvalidArgumentException
{
    public function __construct(
        string $method,
        string $assertion,
        string $message,
    ) {
        parent::__construct(
            \sprintf(
                'Assertion failed in %s: %s. %s',
                $method,
                $assertion,
                $message,
            ),
        );
    }
}
