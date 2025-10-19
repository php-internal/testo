<?php

declare(strict_types=1);

namespace Testo;

use Testo\Common\Container;
use Testo\Common\Filter;
use Testo\Common\Internal\Bootstrap;
use Testo\Config\ApplicationConfig;
use Testo\Test\Dto\RunResult;
use Testo\Test\Runner\SuiteRunner;
use Testo\Test\SuiteProvider;

final class Application
{
    private function __construct(
        /**
         * @internal
         */
        public readonly Container $container,
    ) {}

    public static function create(
        ApplicationConfig $config,
    ): self {
        $container = Bootstrap::init()
            ->withConfig($config->services)
            ->finish();
        $container->set($config);
        return new self($container);
    }

    public function run($filter = new Filter()): RunResult
    {
        $suiteResults = [];

        $suiteProvider = $this->container->get(SuiteProvider::class);
        $suiteRunner = $this->container->get(SuiteRunner::class);

        # Iterate Test Suites
        foreach ($suiteProvider->withFilter($filter)->getSuites() as $suite) {
            $suiteResults[] = $suiteRunner->runSuite($suite, $filter);
        }

        # Run suites
        return new RunResult($suiteResults);
    }
}
