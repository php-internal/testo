<?php

declare(strict_types=1);

namespace Testo\Test\Definition;

use Testo\Test\Dto\TestDefinitions;

final class CaseDefinition
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?\ReflectionClass $reflection = null,
        public readonly TestDefinitions $tests = new TestDefinitions(),
        // public ?string $runner = null,
    ) {}

    public function setName(
        ?string $name = null,
    ): self {
        return new self(
            $name ?? $this->name,
            $this->reflection,
            $this->tests,
        );
    }

    public function getName(): string
    {
        return $this->name ?? 'undefined';
    }
}
