<?php

namespace App\Command\Admin;

use App\Command\CommandTrait;
use App\Service\DumpDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour créer une sauvegarde de la base de données.
 */
class DatabaseBackupCommand extends Command
{
    use CommandTrait;

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
            $this->writeMessage('success', 'Backup of database is successful !');

            return Command::SUCCESS;
        }

        $this->writeMessage('error', 'Backup of database is failed !');

        return Command::FAILURE;
    }
}
