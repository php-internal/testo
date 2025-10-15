<?php

declare(strict_types=1);

namespace Testo;

use Testo\Config\ApplicationConfig;
use Testo\Dto\Filter;
use Testo\Dto\Run\RunResult;
use Testo\Internal\Bootstrap;
use Testo\Internal\Container;
use Testo\Suite\SuiteProvider;
use Testo\Suite\SuiteRunner;

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
    ) {
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
            $suiteResults[] = $suiteRunner->run($suite, $filter);
        }

        # Run suites
        return new RunResult($suiteResults);
    }
}
