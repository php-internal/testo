<?php

declare(strict_types=1);

namespace Testo\Suite;

use Testo\Dto\Filter;
use Testo\Suite\Dto\SuiteInfo;
use Testo\Suite\Dto\SuiteResult;
use Testo\Test\CaseRunner;
use Testo\Test\Dto\CaseInfo;

/**
 * A test suite runner that executes a suite of tests and returns the results.
 */
final class SuiteRunner
{
    public function __construct(
        private readonly CaseRunner $caseRunner,
    ) {}

    public function run(SuiteInfo $suite, Filter $filter): SuiteResult
    {
        # Apply suite name filter if exists
        $suite->name === null or $filter = $filter->withTestSuites($suite->name);

        // todo if random, run in random order?

        $runner = $this->caseRunner;
        $results = [];
        # Run tests for each case
        foreach ($suite->testCases->getCases() as $caseDefinition) {
            try {
                $caseInfo = new CaseInfo(
                    definition: $caseDefinition,
                );
                $results[] = $runner->runCase($caseInfo, $filter);
            } catch (\Throwable) {
                // Skip for now
            }
        }

        return new SuiteResult($results);
    }
}
