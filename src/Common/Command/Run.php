<?php

declare(strict_types=1);

namespace Testo\Common\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Testo\Common\Filter;
use Testo\Render\StdoutRenderer;
use Testo\Render\TeamcityInterceptor;
use Testo\Render\TerminalInterceptor;

#[AsCommand(
    name: 'run',
)]
final class Run extends Base
{
    public function configure(): void
    {
        parent::configure();
        $this->addOption('teamcity', null, InputOption::VALUE_NONE);
        $this->addOption('filter', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "Test suites to be run");
    }

    public function __invoke(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        if ($input->getOption('teamcity')) {
            $this->container->bind(StdoutRenderer::class, TeamcityInterceptor::class);
        } else {
            $this->container->bind(StdoutRenderer::class, TerminalInterceptor::class);
        }

        $filter = new Filter();
        $filterOptions = $input->getOption('filter');
        if ($filterOptions) {
            $filter = $filter->withTestSuites(...$filterOptions);
        }
        $result = $this->application->run($filter);

        return $result->status->isSuccessful()
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}
