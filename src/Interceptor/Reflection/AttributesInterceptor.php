<?php

declare(strict_types=1);

namespace Testo\Interceptor\Reflection;

use Testo\Attribute\Interceptable;
use Testo\Interceptor\TestCaseRunInterceptor;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Module\Interceptor\InterceptorProvider;
use Testo\Module\Interceptor\Internal\Pipeline;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Reads {@see Interceptable} attributes and integrates them into the pipeline.
 */
final class AttributesInterceptor implements TestRunInterceptor, TestCaseRunInterceptor
{
    public function __construct(
        private readonly InterceptorProvider $interceptorProvider,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $classAttributes = $info->caseInfo->definition->reflection === null
            ? []
            : Reflection::fetchClassAttributes(
                class: $info->caseInfo->definition->reflection,
                attributeClass: Interceptable::class,
                flags: \ReflectionAttribute::IS_INSTANCEOF,
            );

        $methodAttributes = Reflection::fetchFunctionAttributes(
            function: $info->testDefinition->reflection,
            attributeClass: Interceptable::class,
            flags: \ReflectionAttribute::IS_INSTANCEOF,
        );

        $attrs = \array_merge($classAttributes, $methodAttributes);
        if ($attrs === []) {
            # No attributes, continue to next interceptor
            return $next($info);
        }

        # Merge and instantiate attributes
        $interceptors = $this->interceptorProvider->fromAttributes(TestRunInterceptor::class, ...\array_map(
            static fn(\ReflectionAttribute $a): Interceptable => $a->newInstance(),
            $attrs,
        ));

        return Pipeline::prepare(...$interceptors)->with(
            $next,
            /** @see TestRunInterceptor::runTest() */
            'runTest',
        )($info);
    }

    public function runTestCase(CaseInfo $info, callable $next): CaseResult
    {
        $attrs = $info->definition->reflection === null
            ? []
            : Reflection::fetchClassAttributes(
                class: $info->definition->reflection,
                attributeClass: Interceptable::class,
                flags: \ReflectionAttribute::IS_INSTANCEOF,
            );

        if ($attrs === []) {
            # No attributes, continue to next interceptor
            return $next($info);
        }

        # Merge and instantiate attributes
        $interceptors = $this->interceptorProvider->fromAttributes(TestCaseRunInterceptor::class, ...\array_map(
            static fn(\ReflectionAttribute $a): Interceptable => $a->newInstance(),
            $attrs,
        ));

        return Pipeline::prepare(...$interceptors)->with(
            $next,
            /** @see TestCaseRunInterceptor::runTestCase() */
            'runTestCase',
        )($info);
    }
}
