<?php

declare(strict_types=1);

namespace Testo\Assert\Interceptor;

use Testo\Assert\State\ExpectedFailure;
use Testo\Assert\StaticState;
use Testo\Attribute\Fail;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

final class FailConfigurator implements TestRunInterceptor
{
    public function __construct(
        private readonly Fail $options,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $context = StaticState::current() ?? throw new \RuntimeException(\sprintf(
            'Interceptor %s must be defined in the pipeline',
            self::class,
        ));

        $context->expectedFailure = new ExpectedFailure(
            message: $this->options->message,
        );

        return $next($info);
    }
}