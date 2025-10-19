<?php

declare(strict_types=1);

namespace Testo\Test\Runner;

use Testo\Interceptor\Exception\PipelineFailure;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Module\Interceptor\InterceptorProvider;
use Testo\Module\Interceptor\Internal\Pipeline;
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
        try {
            # Build interceptors pipeline
            $interceptors = $this->interceptorProvider->fromConfig(TestRunInterceptor::class);

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
                            info: $info,
                            status: Status::Passed,
                            result: $result,
                        );
                    } catch (\Throwable $throwable) {
                        return new TestResult(
                            info: $info,
                            status: Status::Error,
                            failure: $throwable,
                        );
                    }
                },
                /** @see TestRunInterceptor::runTest() */
                'runTest',
            )($info);
        } catch (\Throwable $e) {
            return new TestResult(
                info: $info,
                status: Status::Aborted,
                failure: new PipelineFailure('Error during test execution pipeline.', previous: $e),
            );
        }
    }
}
