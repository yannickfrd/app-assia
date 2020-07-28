<?php

namespace App\Command;

use App\Service\DumpDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->setDescription('Créer une sauvegarde de la base de données.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = 'backups/esperer95.app/'.date('Y/m/');

        $this->dumpDatabase->dump($path);

        $message = '[OK] Backup de la base de données réussi !';
        $output->writeln("\e[32m".$message."\e[0m \n");

        return 0;
    }
}
