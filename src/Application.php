<?php

declare(strict_types=1);

namespace Testo;

use Testo\Common\Container;
use Testo\Common\Filter;
use Testo\Common\Internal\ObjectContainer;
use Testo\Common\Path;
use Testo\Config\ApplicationConfig;
use Testo\Config\Internal\ConfigInflector;
use Testo\Config\ServicesConfig;
use Testo\Test\Dto\RunResult;
use Testo\Test\Dto\Status;
use Testo\Test\Runner\SuiteRunner;
use Testo\Test\SuiteProvider;

final class Application
{
    private function __construct(
        private readonly ObjectContainer $container,
    ) {
        $config = $container->get(ApplicationConfig::class);
        $this->setServices($config->services);
        $this->container->set($config);
    }

    /**
     * Create the application instance with the provided configuration.
     */
    public static function createFromConfig(
        ApplicationConfig $config,
    ): self {
        $container = new ObjectContainer();
        $container->set($config);
        return new self($container);
    }

    /**
     * Create the application instance from ENV, CLI arguments, and config file.
     *
     * @param Path|null $configFile Path to config file
     * @param array<string, mixed> $inputOptions Command-line options
     * @param array<string, mixed> $inputArguments Command-line arguments
     * @param array<string, string> $environment Environment variables
     */
    public static function createFromInput(
        ?Path $configFile = null,
        array $inputOptions = [],
        array $inputArguments = [],
        array $environment = [],
    ): self {
        $container = new ObjectContainer();
        $args = [
            'env' => $environment,
            'inputArguments' => $inputArguments,
            'inputOptions' => $inputOptions,
        ];

        # Bind reading provided config file
        $configFile === null or $container
            ->bind(ApplicationConfig::class, function () use ($configFile): ApplicationConfig {
                $cfg = include $configFile;
                $cfg instanceof ApplicationConfig or throw new \InvalidArgumentException(
                    \sprintf(
                        'Configuration file %s must return an instance of %s, %s returned.',
                        $configFile,
                        ApplicationConfig::class,
                        \is_object($cfg) ? \get_class($cfg) : \gettype($cfg),
                    ),
                );
                return $cfg;
            });

        # Register Config inflector
        $container->addInflector($container->make(ConfigInflector::class, $args));

        return new self($container);
    }

    public function run($filter = new Filter()): RunResult
    {
        $suiteResults = [];

        $suiteProvider = $this->container->get(SuiteProvider::class);
        $suiteRunner = $this->container->get(SuiteRunner::class);
        $status = Status::Passed;

        # Iterate Test Suites
        foreach ($suiteProvider->withFilter($filter)->getSuites() as $suite) {
            $suiteResults[] = $suiteResult = $suiteRunner->runSuite($suite, $filter);
            $suiteResult->status->isFailure() and $status = Status::Failed;
        }

        # Run suites
        return new RunResult($suiteResults, status: $status);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Configures the container with the provided application services configuration.
     *
     * Registers core services and bindings.
     */
    private function setServices(
        ServicesConfig $config,
    ): void {
        foreach ($config as $id => $service) {
            $this->container->bind($id, $service);
        }
    }
}
