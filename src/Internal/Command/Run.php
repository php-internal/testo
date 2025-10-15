<?php

declare(strict_types=1);

namespace Testo\Internal\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'run',
)]
final class Run extends Base
{
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $result = $this->application->run();
        return Command::SUCCESS;
    }
}
