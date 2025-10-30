<?php

declare(strict_types=1);

namespace Testo\Test\Runner;

use Testo\Common\Filter;
use Testo\Interceptor\TestCaseRunInterceptor;
use Testo\Module\Interceptor\InterceptorProvider;
use Testo\Module\Interceptor\Internal\Pipeline;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

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
         * @see TestCaseRunInterceptor::runTestCase()
         * @var list<TestCaseRunInterceptor> $interceptors
         * @var callable(CaseInfo): CaseResult $pipeline
         */
        $interceptors = $this->interceptorProvider->fromConfig(TestCaseRunInterceptor::class);
        $pipeline = Pipeline::prepare(...$interceptors)
            ->with(
                $this->run(...),
                'runTestCase',
            );

        return $pipeline($info);
    }

    public function run(CaseInfo $info): CaseResult
    {
        $results = [];
        $status = Status::Passed;
        foreach ($info->definition->tests->getTests() as $name => $testDefinition) {
            try {
                $testInfo = new TestInfo(
                    name: $name,
                    caseInfo: $info,
                    testDefinition: $testDefinition,
                );

                $result = $this->testRunner->runTest($testInfo);
                $result->status->isFailure() and $status = Status::Failed;

                $results[] = $result;
            } catch (\Throwable $throwable) {
                $status = Status::Error;
                isset($testInfo) and $results[] = new TestResult(
                    info: $testInfo,
                    status: Status::Error,
                    failure: $throwable,
                );
            }
        }

        return new CaseResult(
            results: $results,
            status: $status,
        );
    }
}
