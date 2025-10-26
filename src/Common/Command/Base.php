<?php

declare(strict_types=1);

namespace Testo\Common\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Testo\Application;
use Testo\Common\Container;
use Testo\Config\ApplicationConfig;
use Testo\Config\FinderConfig;
use Testo\Config\SuiteConfig;
use Yiisoft\Injector\Injector;

/**
 * Base abstract class for all commands.
 *
 * Provides common functionality for command initialization, container setup,
 * and configuration handling.
 *
 * ```
 *  // Extend to create a custom command
 *  final class CustomCommand extends Base
 *  {
 *      protected function __invoke(
 *          InputInterface $input,
 *          OutputInterface $output,
 *          ClassName $anyParam): int
 *      {
 *          // Return a Command code
 *          return Command::SUCCESS;
 *      }
 *  }
 * ```
 *
 * @internal
 */
abstract class Base extends Command
{
    /** @var Container IoC container with services */
    protected Container $container;

    protected Application $application;

    /**
     * Configures command options.
     *
     * Adds option for specifying configuration file location.
     */
    public function configure(): void
    {
        parent::configure();
        $this->addOption('config', null, InputOption::VALUE_OPTIONAL, 'Path to the configuration file');
    }

    /**
     * Initializes the command execution environment.
     *
     * Sets up logger, container, and registers input/output in the container.
     *
     * @param InputInterface $input Command input
     * @param OutputInterface $output Command output
     *
     * @return int Command success code
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $cfg = new ApplicationConfig(
            src: new FinderConfig(['src']),
            suites: [
                new SuiteConfig(
                    name: 'default',
                    location: new FinderConfig(['tests/Testo']),
                ),
            ],
        );

        $this->application = Application::createFromConfig($cfg);
        $this->container = $this->application->getContainer();

        $this->container->set($input, InputInterface::class);
        $this->container->set($output, OutputInterface::class);
        $this->container->set(new SymfonyStyle($input, $output), StyleInterface::class);

        return $this->container->get(Injector::class)->invoke($this) ?? Command::SUCCESS;
    }
}
