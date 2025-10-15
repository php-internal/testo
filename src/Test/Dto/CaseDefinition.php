<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

final class CaseDefinition
{
    public function __construct(
        public readonly ?\ReflectionClass $reflection = null,
        public readonly TestDefinitions $tests = new TestDefinitions(),
        // public ?string $runner = null,
    ) {}
}
