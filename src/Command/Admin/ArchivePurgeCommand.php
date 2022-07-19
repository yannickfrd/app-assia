<?php

namespace App\Command\Admin;

use App\Service\Admin\Archiver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ArchivePurgeCommand extends Command
{
    protected static $defaultName = 'app:admin:archive-purge';

    private $archiver;

    public function __construct(Archiver $archiver)
    {
        parent::__construct();

        $this->archiver = $archiver;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Archive or delete datas.')
            ->addOption('archive', 'a', InputOption::VALUE_NONE, 'Archive datas (supports, people, groups...)')
            ->addOption('delete', 'd', InputOption::VALUE_NONE, 'Purge deleted datas')
            ->addOption('render-table', 'r', InputOption::VALUE_NONE, 'Render the table with stats')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $noInteraction = $input->getOption('no-interaction');
        $renderTable = $input->getOption('render-table');

        if ($renderTable) {
            $this->statsRender($io);
        }

        if ($io->confirm('Do you want to archive datas?', false)
            || ($input->getOption('archive') && $noInteraction)) {
            $this->archiver->archive();

            if (!$noInteraction) {
                $io->success('Archiving is successful!');
            }
        }

        if ($io->confirm('Do you want to purge datas?', false)
            || ($input->getOption('delete') && $noInteraction)) {
            $this->archiver->purge();

            if (!$noInteraction) {
                $io->success('Purge is successful!');
            }
        }

        return Command::SUCCESS;
    }

    protected function statsRender(SymfonyStyle $io): void
    {
        $stats = $this->archiver->getStats();

        $io->table(
            ['', 'To archive', 'To purge'],
            [
                ['SupportGroup', $stats['support_groups']['archive_count'], $stats['support_groups']['purge_count']],
                ['PeopleGroup', $stats['people_groups']['archive_count'], $stats['people_groups']['purge_count']],
                ['Person', $stats['people']['archive_count'], $stats['people']['purge_count']],
                ['Document', $stats['documents']['archive_count'], $stats['documents']['purge_count']],
                ['Note', $stats['notes']['archive_count'], $stats['notes']['purge_count']],
                ['Payment', $stats['payments']['archive_count'], $stats['payments']['purge_count']],
                ['Rdv', $stats['rdvs']['archive_count'], $stats['rdvs']['purge_count']],
                ['Task', $stats['tasks']['archive_count'], $stats['tasks']['purge_count']],
            ]
        );
    }
}
