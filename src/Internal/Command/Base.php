<?php

declare(strict_types=1);

namespace Testo\Internal\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Testo\Internal\Bootstrap;
use Testo\Internal\Service\Container;
use Yiisoft\Injector\Injector;

/**
 * Base abstract class for all commands.
 *
 * Provides common functionality for command initialization, container setup,
 * and configuration handling.
 *
 * ```php
 * // Extend to create a custom command
 * final class CustomCommand extends Base
 * {
 *     protected function execute(InputInterface $input, OutputInterface $output): int
 *     {
 *         parent::execute($input, $output);
 *         // Command implementation
 *         return Command::SUCCESS;
 *     }
 * }
 * ```
 *
 * @internal
 */
abstract class Base extends Command
{
    /** @var Container IoC container with services */
    protected Container $container;

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
        $this->container = $container = Bootstrap::init()->withConfig()->finish();

        $container->set($input, InputInterface::class);
        $container->set($output, OutputInterface::class);
        $container->set(new SymfonyStyle($input, $output), StyleInterface::class);

        return (new Injector($container))->invoke($this) ?? Command::SUCCESS;
    }
}
