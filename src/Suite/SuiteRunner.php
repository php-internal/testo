<?php

declare(strict_types=1);

namespace Testo\Suite;

use Testo\Dto\Filter;
use Testo\Suite\Dto\SuiteInfo;
use Testo\Suite\Dto\SuiteResult;
use Testo\Test\CaseRunner;
use Testo\Test\TestsProvider;

/**
 * A test suite runner that executes a suite of tests and returns the results.
 */
final class SuiteRunner
{
    public function __construct(
        private readonly TestsProvider $testProvider,
        private readonly CaseRunner $caseRunner,
    ) {}

    public function run(SuiteInfo $suite, Filter $filter): SuiteResult
    {
        # Apply suite name filter if exists
        $suite->name === null or $filter = $filter->withTestSuites($suite->name);

        # Get tests
        $cases = $this->testProvider
            ->withFilter($filter)
            ->getCases();

        // todo if random, run in random order

        # Run tests in each case
        foreach ($cases as $case) {
            $this->caseRunner->runCase(
                $case,
                $case->reflection === null ? $filter : $filter->withTestCases($case->name),
            );
        }

        return new SuiteResult([]);
    }
}
