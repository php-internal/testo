<?php

declare(strict_types=1);

namespace Testo\Common\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Testo\Render\StdoutRenderer;
use Testo\Render\TeamcityInterceptor;
use Testo\Render\TerminalInterceptor;

#[AsCommand(
    name: 'run',
)]
final class Run extends Base
{
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        // $this->container->bind(StdoutRenderer::class, TeamcityInterceptor::class);
        $this->container->bind(StdoutRenderer::class, TerminalInterceptor::class);

        $result = $this->application->run();

        return $result->status->isSuccessful()
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}
