<?php

namespace App\Command\Admin;

use App\Command\CommandTrait;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour supprimer toutes les items en cache dans le pool.
 */
class CacheClearCommand extends Command
{
    use CommandTrait;

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

        $this->writeMessage('success', 'Items in the pool were successfully cleared !');

        return Command::SUCCESS;
    }
}
