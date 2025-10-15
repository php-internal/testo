<?php

declare(strict_types=1);

namespace Tests\Unit\Test;

use PHPUnit\Framework\TestCase;
use Testo\Interceptor\InterceptorProvider;
use Testo\Test\Dto\CaseDefinition;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestDefinition;
use Testo\Test\TestRunner;
use Tests\Fixture\TestInterceptors;

final class TestsRunnerTest extends TestCase
{
    /**
     * @see TestInterceptors::withRetryPolicy()
     */
    public function testRunMethodWithRetry(): void
    {
        $instance = self::createInstance();
        $info = new TestInfo(
            caseInfo: new CaseInfo(
                definition: new CaseDefinition(
                    reflection: new \ReflectionClass(TestInterceptors::class),
                ),
                instance: new TestInterceptors(),
            ),
            testDefinition: new TestDefinition(
                reflection: new \ReflectionMethod(TestInterceptors::class, 'withRetryPolicy'),
            ),
        );

        $result = $instance->runTest($info);

        self::assertSame(3, $result->result);
        self::assertSame(Status::Flaky, $result->status);
    }

    /**
     * @see \Tests\Fixture\withRetryPolicy()
     */
    public function testRunFunctionWithRetry(): void
    {
        $instance = self::createInstance();
        $info = new TestInfo(
            caseInfo: new CaseInfo(
                instance: new TestInterceptors(),
            ),
            testDefinition: new TestDefinition(
                reflection: new \ReflectionFunction(\Tests\Fixture\withRetryPolicy(...)),
            ),
        );

        $result = $instance->runTest($info);

        self::assertSame(3, $result->result);
        self::assertSame(Status::Passed, $result->status);
    }

    private static function createInstance(): TestRunner
    {
        return new TestRunner(InterceptorProvider::createDefault());
    }
}
