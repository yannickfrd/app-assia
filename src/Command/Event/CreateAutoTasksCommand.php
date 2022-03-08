<?php

namespace App\Command\Event;

use App\Service\Event\AutoTasksGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateAutoTasksCommand extends Command
{
    protected static $defaultName = 'app:task:create-auto-tasks';
    protected static $defaultDescription = 'Create auto tasks with alert from evaluation informations.';

    private $autoTasksGenerator;

    public function __construct(AutoTasksGenerator $autoTasksGenerator)
    {
        $this->autoTasksGenerator = $autoTasksGenerator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $nbTasks = $this->autoTasksGenerator->generate($io);

        $io->success(number_format($nbTasks, 0, ',', ' ').' auto tasks are created!');

        return Command::SUCCESS;
    }
}
