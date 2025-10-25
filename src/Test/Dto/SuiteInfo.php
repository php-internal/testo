<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

final class SuiteInfo
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,
        public readonly CaseDefinitions $testCases,
    ) {}
}
