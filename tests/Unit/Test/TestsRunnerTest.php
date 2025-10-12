<?php

declare(strict_types=1);

namespace Tests\Unit\Test;

use PHPUnit\Framework\TestCase;
use Testo\Dto\Case\CaseDefinition;
use Testo\Dto\Test\TestDefinition;
use Testo\Interceptor\InterceptorProvider;
use Testo\Test\TestsRunner;
use Tests\Fixture\TestInterceptors;

final class TestsRunnerTest extends TestCase
{
    public function testRunMethodWithRetry(): void
    {
        $instance = self::createInstance();
        $caseDefinition = new CaseDefinition(
            reflection: new \ReflectionClass(TestInterceptors::class),
        );
        $testDefinition = new TestDefinition(
            /** @see TestInterceptors::withRetryPolicy() */
            reflection: $caseDefinition->reflection->getMethod('withRetryPolicy'),
        );

        $result = $instance->run($caseDefinition, $testDefinition);

        self::assertSame(3, $result->result);
    }

    public function testRunFunctionWithRetry(): void
    {
        $instance = self::createInstance();
        $caseDefinition = new CaseDefinition(
            reflection: null,
        );
        $testDefinition = new TestDefinition(
            /** @see \Tests\Fixture\withRetryPolicy() */
            reflection: new \ReflectionFunction(\Tests\Fixture\withRetryPolicy(...)),
        );

        $result = $instance->run($caseDefinition, $testDefinition);

        self::assertSame(3, $result->result);
    }

    private static function createInstance(): TestsRunner
    {
        return new TestsRunner(InterceptorProvider::createDefault());
    }
}
