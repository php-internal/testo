<?php

declare(strict_types=1);

namespace Testo\Test\Runner;

use Testo\Common\Filter;
use Testo\Interceptor\TestSuiteRunInterceptor;
use Testo\Module\Interceptor\InterceptorProvider;
use Testo\Module\Interceptor\Internal\Pipeline;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\SuiteInfo;
use Testo\Test\Dto\SuiteResult;

/**
 * A test suite runner that executes a suite of tests and returns the results.
 */
final class SuiteRunner
{
    public function __construct(
        private readonly CaseRunner $caseRunner,
        private readonly InterceptorProvider $interceptorProvider,
    ) {}

    public function runSuite(SuiteInfo $info, Filter $filter): SuiteResult
    {
        /**
         * Prepare interceptors pipeline
         *
         * @see TestSuiteRunInterceptor::runTestSuite()
         * @var list<TestSuiteRunInterceptor> $interceptors
         * @var callable(SuiteInfo): SuiteResult $pipeline
         */
        $interceptors = $this->interceptorProvider->fromConfig(TestSuiteRunInterceptor::class);
        $pipeline = Pipeline::prepare(...$interceptors)
            ->with(
                fn(SuiteInfo $info): SuiteResult => $this->run($info, $filter),
                'runTestSuite',
            );

        return $pipeline($info);
    }

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
