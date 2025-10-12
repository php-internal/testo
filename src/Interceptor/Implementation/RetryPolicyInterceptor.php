<?php

declare(strict_types=1);

namespace Testo\Interceptor\Implementation;

use Testo\Attribute\RetryPolicy;
use Testo\Dto\Test\TestResult;
use Testo\Interceptor\RunTest\Input;
use Testo\Interceptor\RunTestInterceptor;

/**
 * Interceptor that retries a test execution based on the provided retry policy.
 *
 * @see RetryPolicy
 */
final class RetryPolicyInterceptor implements RunTestInterceptor
{
    public function __construct(
        private readonly RetryPolicy $options,
    ) {}

    /**
     * @throws \Throwable If all retry attempts fail, the last exception is thrown.
     */
    public function runTest(Input $dto, callable $next): TestResult
    {
        $attempts = $this->options->maxAttempts;
        $isFlaky = false;

        run:
        --$attempts;
        try {
            $result = $next($dto);
            # TODO set flaky status on result if $isFlaky is true
            return $isFlaky ? $result : $result;
        } catch (\Throwable $e) {
            # No more attempts left, rethrow the exception
            $attempts > 0 or throw $e;

            $isFlaky = true;
            unset($e);
            goto run;
        }
    }
}
