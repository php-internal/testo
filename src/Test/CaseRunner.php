<?php

declare(strict_types=1);

namespace Testo\Test;

use Testo\Dto\Filter;
use Testo\Interceptor\InterceptorProvider;
use Testo\Interceptor\Internal\Pipeline;
use Testo\Interceptor\TestCaseCallInterceptor;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\TestInfo;

final class CaseRunner
{
    public function __construct(
        private readonly TestRunner $testRunner,
        private readonly InterceptorProvider $interceptorProvider,
    ) {}

    public function runCase(CaseInfo $info, Filter $filter): CaseResult
    {
        # TODO handle async tests
        # TODO handle random order

        /**
         * Prepare interceptors pipeline
         *
         * @see TestCaseCallInterceptor::runTestCase()
         * @var list<TestCaseCallInterceptor> $interceptors
         * @var callable(CaseInfo): CaseResult $pipeline
         */
        $interceptors = $this->interceptorProvider->fromClasses(TestCaseCallInterceptor::class);

        // todo remove
        $interceptors[] = new TestCaseCallInterceptor\InstantiateTestCase();

        $pipeline = Pipeline::prepare(...$interceptors)
            ->with(
                fn(CaseInfo $info): CaseResult => $this->run($info),
                'runTestCase',
            );

        return $pipeline($info);
    }

    public function run(CaseInfo $info): CaseResult
    {
        $results = [];
        foreach ($info->definition->tests->getTests() as $testDefinition) {
            $testInfo = new TestInfo(
                caseInfo: $info,
                testDefinition: $testDefinition,
            );

            $results[] = $this->testRunner->runTest($testInfo);
        }

        return new CaseResult($results);
    }
}
