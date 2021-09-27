<?php

namespace App\Command;

use App\Service\DumpDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour créer une sauvegarde de la base de données.
 */
class DatabaseBackupCommand extends Command
{
    protected static $defaultName = 'app:database:backup';

    protected $dumpDatabase;

    public function __construct(DumpDatabase $dumpDatabase)
    {
        $this->dumpDatabase = $dumpDatabase;

        parent::__construct();
    }

    protected function configure()
    {
        // $this->setName('app:database:backup');
        $this->setAliases(['app:db:b']);
        $this->setDescription('Create a backup of database.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = 'backups/app-assia/'.date('Y/m/');

        $dump = $this->dumpDatabase->dump($path);

        if (0 === $dump['return']) {
            $message = "\n[OK] Backup of database is successful !\n";
            $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

            return Command::SUCCESS;
        }
        $message = "\n[Error] Backup of database is failed !\n";
        $output->writeln("\e[30m\e[41m\n ".$message."\e[0m\n");

        return Command::FAILURE;
    }
}
