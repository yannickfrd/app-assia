<?php

namespace App\Command\Admin;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cache:clear',
    description: 'Delete all items in the cache pool.',
)]
class CacheClearCommand extends Command
{
    protected $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->cache->clear();

        $io->success('Items in the pool were successfully cleared !');

        return Command::SUCCESS;
    }
}
