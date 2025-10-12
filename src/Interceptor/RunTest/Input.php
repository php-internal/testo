<?php

declare(strict_types=1);

namespace Testo\Interceptor\RunTest;

use Testo\Dto\Test\TestDefinition;

final class Input
{
    public function __construct(
        public readonly TestDefinition $definition,
        /** Test Case Class Instance if exists */
        public readonly ?object $instance,
    ) {}
}
