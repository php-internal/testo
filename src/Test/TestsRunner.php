<?php

declare(strict_types=1);

namespace Testo\Test;

use Testo\Attribute\Interceptable;
use Testo\Interceptor\InterceptorProvider;
use Testo\Interceptor\Internal\Pipeline;
use Testo\Interceptor\RunTestInterceptor;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

final class TestsRunner
{
    public function __construct(
        private readonly InterceptorProvider $interceptorProvider,
    ) {}

    public function runTest(TestInfo $info): TestResult
    {
        # Build interceptors pipeline
        $interceptors = $this->prepareInterceptors($info);
        return Pipeline::prepare(...$interceptors)->with(
            static function (TestInfo $info): TestResult {
                # TODO resolve arguments
                $result = $info->instance === null
                    ? $info->testDefinition->reflection->invoke()
                    : $info->testDefinition->reflection->invoke($info->instance);

                return new TestResult(
                    $info,
                    $result,
                    Status::Passed,
                );
            },
            /** @see RunTestInterceptor::runTest() */
            'runTest',
        )($info);
    }

    /**
     * @return list<RunTestInterceptor>
     */
    private function prepareInterceptors(TestInfo $info): array
    {
        $classAttributes = $info->caseInfo->definition->reflection?->getAttributes(
            Interceptable::class,
            \ReflectionAttribute::IS_INSTANCEOF,
        ) ?? [];
        $methodAttributes = $info->testDefinition->reflection->getAttributes(
            Interceptable::class,
            \ReflectionAttribute::IS_INSTANCEOF,
        );

        # Merge and instantiate attributes
        $attrs = \array_map(
            static fn(\ReflectionAttribute $a): Interceptable => $a->newInstance(),
            \array_merge($classAttributes, $methodAttributes),
        );

        return $this->interceptorProvider->fromAttributes(RunTestInterceptor::class, ...$attrs);
    }
}
