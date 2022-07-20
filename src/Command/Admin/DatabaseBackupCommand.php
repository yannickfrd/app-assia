<?php

namespace App\Command\Admin;

use App\Service\DatabaseDumper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:database:backup',
    description: 'Create a dump of database.',
)]
class DatabaseBackupCommand extends Command
{
    protected $databaseDumper;

    public function __construct(DatabaseDumper $databaseDumper)
    {
        $this->databaseDumper = $databaseDumper;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Path to save backup of database.', null)
            ->addOption('zipped', 'z', InputOption::VALUE_OPTIONAL, 'Gzip compression option', 'yes')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getOption('path');
        $zipped = in_array($input->getOption('zipped'), ['no', 'n']) ? false : true;

        $dump = $this->databaseDumper->dump($path, $zipped);

        if (0 != $dump['resultCode']) {
            $io->error('Backup of database is failed !');

            return Command::FAILURE;
        }

        $io->success('Backup of database is successful !');

        return Command::SUCCESS;
    }
}
