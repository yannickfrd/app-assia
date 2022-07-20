<?php

namespace App\Command\Event;

use App\Service\Event\AutoTasksGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:task:create-auto-tasks',
    description: 'Create auto tasks with alert from evaluation informations.',
)]
class CreateAutoTasksCommand extends Command
{
    private $autoTasksGenerator;

    public function __construct(AutoTasksGenerator $autoTasksGenerator)
    {
        $this->autoTasksGenerator = $autoTasksGenerator;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nbTasks = $this->autoTasksGenerator->generate($io);

        $io->success(number_format($nbTasks, 0, ',', ' ').' auto tasks are created!');

        return Command::SUCCESS;
    }
}
