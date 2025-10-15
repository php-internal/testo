<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

/**
 * Information about run test.
 */
final class TestInfo
{
    public function __construct(
        public readonly CaseInfo $caseInfo,
        public readonly TestDefinition $testDefinition,
    ) {}
}
