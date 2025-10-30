<?php

declare(strict_types=1);

namespace Testo\Sample\Internal;

use Testo\Attribute\RetryPolicy;
use Testo\Sample\DataProvider;
use Testo\Sample\MultipleResult;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Interceptor that retries a test execution based on the provided retry policy.
 *
 * @see RetryPolicy
 */
final class DataProviderInterceptor implements TestRunInterceptor
{
    public function __construct(
        private readonly DataProvider $options,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        // Fetch data sets from the provider
        $dataSets = ($this->options->provider)();

        // Run the test for each data set
        $results = [];
        $status = Status::Passed;
        foreach ($dataSets as $k => $dataSet) {
            $label = (string) $k;
            $i = 0;
            while (\array_key_exists($label, $results)) {
                ++$i;
                $label = "$k~$i";
            }

            $i = $info->with($dataSet);
            try {
                $result = $next($i);
            } catch (\Throwable $throwable) {
                $result = new TestResult(
                    info: $i,
                    status: Status::Error,
                    failure: $throwable,
                );
            }

            unset($dataSet, $i);
            $result->status->isFailure() and $status = Status::Failed;
            $results[$label] = $result;
        }

        $results = new MultipleResult($results);

        return new TestResult(
            info: $info,
            status: $status,
            result: $results,
            attributes: [
                MultipleResult::class => $results,
            ],
        );
    }
}
