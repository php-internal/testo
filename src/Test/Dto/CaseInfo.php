<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

/**
 * Information about run test case.
 */
final class CaseInfo
{
    public function __construct(
        public readonly CaseDefinition $definition,
    ) {}
}
