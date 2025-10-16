<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Interceptor\TestCallInterceptor;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Collects assertions.
 *
 * Creates a new {@see AssertCollector} instance for each test and assigns it to the {@see StaticState}.
 * After the test is executed, the collector is attached to the {@see TestResult} attributes.
 *
 * Supports both synchronous and asynchronous (Fiber-based) environments.
 */
final class AssertCollectorInterceptor implements TestCallInterceptor
{
    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $collector = new AssertCollector();
        try {
            $previous = StaticState::swap($collector);

            if (\Fiber::getCurrent() === null) {
                # No Fiber, run the test directly
                $result = $next($info);
            } else {
                # Create a Fiber scope to run the test
                $fiber = new \Fiber(static fn(): TestResult => $next($info));

                $value = $fiber->start();
                while (!$fiber->isTerminated()) {
                    StaticState::swap($previous);
                    try {
                        $resume = \Fiber::suspend($value);
                    } catch (\Throwable $e) {
                        $previous = StaticState::swap($collector);
                        $value = $fiber->throw($e);
                        continue;
                    }

                    $previous = StaticState::swap($collector);
                    $value = $fiber->resume($resume);
                }

                /** @var TestResult $result */
                $result = $fiber->getReturn();
            }

            return $result->withAttribute(AssertCollector::class, $collector);
        } finally {
            StaticState::swap($previous);
        }
    }
}
