<?php

declare(strict_types=1);

namespace Testo\Test;

use Testo\Dto\Filter;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\TestInfo;

final class CaseRunner
{
    public function __construct(
        private readonly TestsRunner $testRunner,
        private readonly TestsProvider $testsProvider,
    ) {}

    public function runCase(CaseInfo $info, Filter $filter): CaseResult
    {
        $results = [];

        # TODO handle async tests

        # Instantiate test case
        # TODO autowire dependencies
        $instance = $info->definition->reflection?->newInstance();

        $tests = $this->testsProvider->withFilter($filter)->getTests();
        foreach ($tests as $testDefinition) {
            $testInfo = new TestInfo(
                caseInfo: $info,
                testDefinition: $testDefinition,
                instance: $instance,
            );
            $results[] = $this->testRunner->runTest($testInfo);
        }

        return new CaseResult($results);
    }
}
