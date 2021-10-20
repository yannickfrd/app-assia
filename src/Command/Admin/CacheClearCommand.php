<?php

namespace App\Command\Admin;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour supprimer toutes les items en cache dans le pool.
 */
class CacheClearCommand extends Command
{
    protected static $defaultName = 'app:cache:clear';
    protected static $defaultDescription = 'Delete all items in the pool.';

    protected $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->cache->clear();

        $io->success('Items in the pool were successfully cleared !');

        return Command::SUCCESS;
    }
}
