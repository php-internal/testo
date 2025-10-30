<?php

declare(strict_types=1);

namespace Testo\Assert\Internal;

use Testo\Assert\Exception\StateNotFound;
use Testo\Assert\Expectation\ExpectedException;
use Testo\Assert\StaticState;
use Testo\Attribute\ExpectException;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Configures expected exceptions for a test based on the {@see ExpectException} attribute.
 */
final class ExpectExceptionConfigurator implements TestRunInterceptor
{
    public function __construct(
        private readonly ExpectException $options,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $context = StaticState::current() ?? throw new StateNotFound();

        $context->expectations[] = new ExpectedException(
            classOrObject: $this->options->class,
        );

        return $next($info);
    }
}
