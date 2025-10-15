<?php

declare(strict_types=1);

namespace Testo\Interceptor\TestCaseCallInterceptor;

use Testo\Interceptor\Exception\TestCaseInstantiationException;
use Testo\Interceptor\TestCaseCallInterceptor;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\CaseResult;

/**
 * Instantiate the test case class if not already instantiated.
 */
final class InstantiateTestCase implements TestCaseCallInterceptor
{
    public function runTestCase(CaseInfo $info, callable $next): CaseResult
    {
        if ($info->instance === null && $info->definition->reflection !== null) {
            // TODO autowire dependencies
            try {
                $instance = $info->definition->reflection->newInstance();
            } catch (\Throwable $e) {
                throw new TestCaseInstantiationException(previous: $e);
            }

            $info = $info->withInstance($instance);
        }

        return $next($info);
    }
}
