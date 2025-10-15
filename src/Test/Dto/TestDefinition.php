<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

final class TestDefinition
{
    public function __construct(
        public readonly \ReflectionFunctionAbstract $reflection,
    ) {}
}
