<?php

declare(strict_types=1);

namespace Tests\Unit\Test;

use PHPUnit\Framework\TestCase;
use Testo\Core\Filter;
use Testo\Core\Internal\Bootstrap;
use Testo\Test\Definition\CaseDefinition;
use Testo\Test\Dto\CaseInfo;
use Testo\Test\Runner\CaseRunner;
use Tests\Fixture\TestInterceptors;

final class CaseRunnerTest extends TestCase
{
    /**
     * @see TestInterceptors
     */
    public function testRunMethodWithRetry(): void
    {
        $instance = self::createInstance();
        $info = new CaseInfo(
            definition: new CaseDefinition(
                name: 'TestInterceptors',
                reflection: new \ReflectionClass(TestInterceptors::class),
            ),
        );

        $result = $instance->runCase($info, new Filter());

        self::assertNotEmpty($result->results);
    }

    private static function createInstance(): CaseRunner
    {
        return Bootstrap::init()->finish()->make(CaseRunner::class);
    }
}
