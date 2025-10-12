<?php

declare(strict_types=1);

namespace Testo\Dto\Test;

final class TestResult
{
    public function __construct(
        public readonly TestDefinition $definition,
        public readonly mixed $result,
    ) {}
}
