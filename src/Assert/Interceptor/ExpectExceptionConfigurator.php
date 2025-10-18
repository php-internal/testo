<?php

declare(strict_types=1);

namespace Testo\Assert\Interceptor;

use Testo\Assert\State\ExpectedException;
use Testo\Assert\StaticState;
use Testo\Attribute\ExpectException;
use Testo\Interceptor\TestCallInterceptor;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

final class ExpectExceptionConfigurator implements TestCallInterceptor
{
    public function __construct(
        private readonly ExpectException $options,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $context = StaticState::current() ?? throw new \RuntimeException(\sprintf(
            'Interceptor %s must be defined in the pipeline',
            AssertCollectorInterceptor::class,
        ));

        $context->expectException = new ExpectedException(
            classOrObject: $this->options->class,
        );

        return $next($info);
    }
}
