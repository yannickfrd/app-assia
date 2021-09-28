<?php

namespace App\Command\Support;

use App\Service\DumpDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour créer les liasons entre le service AVDL et les différents prescripteurs.
 */
class AvdlOrganizationsCommand extends Command
{
    protected static $defaultName = 'app:service_organization:insert:avdl';

    protected $dumpDatabase;

    public function __construct(DumpDatabase $dumpDatabase)
    {
        $this->dumpDatabase = $dumpDatabase;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Insert links beetween AVDL service and organizations.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = '"INSERT INTO service_organization (service_id, organization_id) VALUES (5, 11), (5, 13), (5, 14), (5, 2),(5, 22), (5, 9), (5, 12)"';
        $cmd = 'php bin/console doctrine:query:sql '.$sql;

        $outputExec = [];
        exec($cmd, $outputExec, $return);

        if (0 === $return) {
            $message = '[OK] Insert is successfull !';
            $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");
        } else {
            $message = '[Error] Insert failed.';
            $output->writeln("\e[37m\e[41m\n ".$message."\e[0m\n");
        }

        return $return;
    }
}
