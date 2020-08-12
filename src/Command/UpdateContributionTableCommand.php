<?php

namespace App\Command;

use App\Service\DumpDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateContributionTableCommand extends Command
{
    protected static $defaultName = 'app:sql:update:contribution';

    protected $dumpDatabase;

    public function __construct(DumpDatabase $dumpDatabase)
    {
        $this->dumpDatabase = $dumpDatabase;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Update the contribution table in database SQL.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = 'ALTER TABLE contribution CHANGE date month_contrib DATE ; ';
        $sql = $sql.'ALTER TABLE contribution CHANGE housing_assitance_amt apl_amt DOUBLE ; ';
        $sql = $sql.'ALTER TABLE contribution CHANGE due_amt to_pay_amt DOUBLE ; ';
        $sql = $sql.'UPDATE contribution SET type=10 WHERE type=2 ; ';
        $sql = $sql.'UPDATE contribution SET type=32 WHERE type=13 ; ';
        $sql = $sql.'UPDATE contribution SET type=31 WHERE type=12 ; ';
        $sql = $sql.'UPDATE contribution SET type=30 WHERE type=11 ; ';
        $sql = $sql.'UPDATE contribution SET type=20 WHERE type=3 ; ';
        $sql = $sql.'UPDATE contribution SET type=11 WHERE type=22 ; ';
        $sql = $sql.'UPDATE contribution SET payment_type=1 WHERE payment_type=2 ; ';
        
        $cmd = 'php bin/console doctrine:query:sql "'.$sql.'"';

        $outputExec = [];
        exec($cmd, $outputExec, $return);

        if ($return == 0) {
            $message = '[OK] Update SQL is successfull !';
            $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");
        } else {
            $message = '[Error] Update SQL failed.';
            $output->writeln("\e[37m\e[41m\n ".$message."\e[0m\n");
        }

        return $return;
    }
}
