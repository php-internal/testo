<?php

declare(strict_types=1);

namespace Testo\Dto\Test;

final class TestDefinition
{
    public function __construct(
        public readonly \ReflectionFunctionAbstract $reflection,
    ) {}
}
