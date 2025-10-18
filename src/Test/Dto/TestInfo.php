<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

use Testo\Test\Definition\TestDefinition;

/**
 * Information about run test.
 */
final class TestInfo
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,
        public readonly CaseInfo $caseInfo,
        public readonly TestDefinition $testDefinition,
    ) {}
}
