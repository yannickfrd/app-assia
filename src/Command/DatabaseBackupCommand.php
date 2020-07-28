<?php

namespace App\Command;

use App\Service\DumpDatabase;
use App\Entity\DatabaseBackup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseBackupCommand extends Command
{
    protected static $defaultName = 'app:database:backup';

    protected $manager;
    protected $dumpDatabase;

    public function __construct(EntityManagerInterface $manager, DumpDatabase $dumpDatabase)
    {
        $this->manager = $manager;
        $this->dumpDatabase = $dumpDatabase;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:database:backup');
        $this->setAliases(['app:db:b']);
        $this->setDescription('Permet de faire une sauvegarde de la base de données.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $backupDatas = $this->dumpDatabase->dump();

        $databaseBackup = (new DatabaseBackup())
            ->setSize($backupDatas['size'])
            ->setZipSize($backupDatas['zipSize'])
            ->setFileName($backupDatas['fileName']);

        $this->manager->persist($databaseBackup);
        $this->manager->flush();

        $message = 'Backup réussi de la base de données !';
        $output->writeln("\e[32m".$message."\e[0m \n");

        return 0;
    }
}
