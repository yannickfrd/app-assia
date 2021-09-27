<?php

namespace App\Command;

use App\Service\DoctrineTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Commande pour supprimer toutes les items en cache dans le pool.
 */
class CacheClearCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:cache:clear';

    protected $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Delete all items in the pool.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cache->clear();

        $message = "[OK] Items in the pool were successfully cleared !\n  ";
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }
}
