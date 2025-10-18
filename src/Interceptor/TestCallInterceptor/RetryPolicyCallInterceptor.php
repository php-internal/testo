<?php

declare(strict_types=1);

namespace Testo\Interceptor\TestCallInterceptor;

use Testo\Attribute\RetryPolicy;
use Testo\Interceptor\TestCallInterceptor;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Interceptor that retries a test execution based on the provided retry policy.
 *
 * @see RetryPolicy
 */
final class RetryPolicyCallInterceptor implements TestCallInterceptor
{
    public function __construct(
        private readonly RetryPolicy $options,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $attempts = $this->options->maxAttempts;
        $isFlaky = false;

        run:
        --$attempts;
        /** @var TestResult $result */
        $result = $next($info);

        if ($result->status->isFailure() && $attempts > 0) {
            # Test failed, check if we can retry
            $isFlaky = true;
            goto run;
        }

        return $isFlaky && $this->options->markFlaky
            ? $result->with(status: Status::Flaky)
            : $result;
    }
}
