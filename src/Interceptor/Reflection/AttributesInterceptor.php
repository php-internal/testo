<?php

declare(strict_types=1);

namespace Testo\Interceptor\Reflection;

use Testo\Attribute\Interceptable;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Module\Interceptor\InterceptorProvider;
use Testo\Module\Interceptor\Internal\Pipeline;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Reads {@see Interceptable} attributes and integrates them into the pipeline.
 */
final class AttributesInterceptor implements TestRunInterceptor
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
}
