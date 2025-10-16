<?php

declare(strict_types=1);

namespace Testo\Test;

use Testo\Assert\AssertCollectorInterceptor;
use Testo\Attribute\Interceptable;
use Testo\Interceptor\InterceptorProvider;
use Testo\Interceptor\Internal\Pipeline;
use Testo\Interceptor\TestCallInterceptor;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

final class TestRunner
{
    public function __construct(
        private readonly InterceptorProvider $interceptorProvider,
    ) {}

    public function runTest(TestInfo $info): TestResult
    {
        # Build interceptors pipeline
        $interceptors = $this->prepareInterceptors($info);

        // todo remove
        $interceptors[] = new AssertCollectorInterceptor();

        return Pipeline::prepare(...$interceptors)->with(
            static function (TestInfo $info): TestResult {
                # TODO resolve arguments
                # TODO don't instantiate if the method is static
                $instance = $info->caseInfo->instance;
                try {
                    $result = $instance === null
                        ? $info->testDefinition->reflection->invoke()
                        : $info->testDefinition->reflection->invoke($instance);

                    return new TestResult(
                        $info,
                        $result,
                        Status::Passed,
                    );
                } catch (\Throwable $throwable) {
                    return new TestResult(
                        $info,
                        $throwable,
                        Status::Failed,
                    );
                }
            },
            /** @see TestCallInterceptor::runTest() */
            'runTest',
        )($info);
    }

    /**
     * @return list<TestCallInterceptor>
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

        return $this->interceptorProvider->fromAttributes(TestCallInterceptor::class, ...$attrs);
    }
}
