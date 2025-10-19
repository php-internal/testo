<?php

declare(strict_types=1);

namespace Testo\Test\Runner;

use Testo\Common\Filter;
use Testo\Interceptor\TestCaseCallInterceptor\InstantiateTestCase;
use Testo\Interceptor\TestCaseRunInterceptor;
use Testo\Module\Interceptor\InterceptorProvider;
use Testo\Module\Interceptor\Internal\Pipeline;
use Testo\Render\StdoutRenderer;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
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
         * @see TestCaseRunInterceptor::runTestCase()
         * @var list<TestCaseRunInterceptor> $interceptors
         * @var callable(CaseInfo): CaseResult $pipeline
         */
        $interceptors = [
            ...$this->interceptorProvider->fromClasses(TestCaseRunInterceptor::class, StdoutRenderer::class), // todo remove
            ...$this->interceptorProvider->fromClasses(TestCaseRunInterceptor::class),
            new InstantiateTestCase(),// todo remove
        ];

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
        foreach ($info->definition->tests->getTests() as $name => $testDefinition) {
            $testInfo = new TestInfo(
                name: $name,
                caseInfo: $info,
                testDefinition: $testDefinition,
            );

            $results[] = $this->testRunner->runTest($testInfo);
        }

        return new CaseResult($results);
    }
}
