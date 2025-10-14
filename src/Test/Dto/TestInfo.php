<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

use Testo\Dto\Filter;

/**
 * Information about run test.
 */
final class TestInfo
{
    public function __construct(
        public readonly CaseInfo $caseInfo,
        public readonly TestDefinition $testDefinition,

        /**
         * Test Case class instance if class is defined, null otherwise.
         */
        public readonly ?object $instance = null,
        private readonly Filter $filter = new Filter(),
    ) {}
}
