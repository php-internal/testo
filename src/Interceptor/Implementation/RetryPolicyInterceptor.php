<?php

declare(strict_types=1);

namespace Testo\Interceptor\Implementation;

use Testo\Attribute\RetryPolicy;
use Testo\Interceptor\RunTestInterceptor;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

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

    #[\Override]
    public function runTest(TestInfo $dto, callable $next): TestResult
    {
        $attempts = $this->options->maxAttempts;
        $isFlaky = false;

        run:
        --$attempts;
        try {
            $result = $next($dto);
            return $isFlaky && $this->options->markFlaky
                ? $result->with(status: Status::Flaky)
                : $result;
        } catch (\Throwable $e) {
            # No more attempts left, rethrow the exception
            $attempts > 0 or throw $e;

            $isFlaky = true;
            unset($e);
            goto run;
        }
    }
}
