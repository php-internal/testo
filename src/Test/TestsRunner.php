<?php

declare(strict_types=1);

namespace Testo\Test;

use Testo\Attribute\Interceptable;
use Testo\Dto\Case\CaseDefinition;
use Testo\Dto\Test\TestDefinition;
use Testo\Dto\Test\TestResult;
use Testo\Interceptor\InterceptorProvider;
use Testo\Interceptor\Internal\Pipeline;
use Testo\Interceptor\RunTest\Input;
use Testo\Interceptor\RunTestInterceptor;

final class TestsRunner
{
    public function __construct(
        private readonly InterceptorProvider $interceptorProvider,
    ) {}

    public function run(CaseDefinition $case, TestDefinition $definition): TestResult
    {
        # Instantiate test case
        # TODO autowire dependencies
        $instance = $case->reflection?->newInstance();

        # Build interceptors pipeline
        $interceptors = $this->prepareInterceptors($definition);
        return Pipeline::prepare(...$interceptors)->with(
            static function (Input $input): TestResult {
                # TODO resolve arguments
                $result = $input->instance === null
                    ? $input->definition->reflection->invoke()
                    : $input->definition->reflection->invoke($input->instance);

                return new TestResult(
                    $input->definition,
                    $result,
                );
            },
            /** @see RunTestInterceptor::runTest() */
            'runTest',
        )(new Input(
            definition: $definition,
            instance: $instance,
        ));
    }

    /**
     * @return list<RunTestInterceptor>
     */
    private function prepareInterceptors(TestDefinition $test)
    {
        $classAttributes = $test->reflection instanceof \ReflectionMethod
            ? $test->reflection->getDeclaringClass()
                ->getAttributes(Interceptable::class, \ReflectionAttribute::IS_INSTANCEOF)
            : [];
        $methodAttributes = $test->reflection
            ->getAttributes(Interceptable::class, \ReflectionAttribute::IS_INSTANCEOF);

        # Merge and instantiate attributes
        $attrs = \array_map(
            static fn(\ReflectionAttribute $a): Interceptable => $a->newInstance(),
            \array_merge($classAttributes, $methodAttributes),
        );

        return $this->interceptorProvider->fromAttributes(RunTestInterceptor::class, ...$attrs);
    }
}
