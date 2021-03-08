<?php

namespace App\Command;

use App\Repository\Support\ContributionRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Met à jour les dates de début et de fin de la période des contributions (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateContributionDateCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:contribution:update:contrib_date';

    protected $repo;
    protected $manager;

    public function __construct(ContributionRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);
        $this->manager->getFilters()->disable('softdeleteable');

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->update();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    protected function update()
    {
        $contributions = $this->repo->findBy([], ['updatedAt' => 'DESC']);
        $count = 0;

        foreach ($this->repo->findAll() as $contribution) {
            $supportGroup = $contribution->getSupportGroup();
            $monthContrib = $contribution->getMonthContrib();
            if ($contribution->getMonthContrib()) {
                $contribution->setStartDate(max($monthContrib, $supportGroup->getStartDate()));
                $lastDay = (clone $monthContrib)->modify('last day of this month');
                $contribution->setEndDate(min($lastDay, $supportGroup->getEndDate() ?? $lastDay));
                ++$count;
            }
        }

        $this->manager->flush();

        return "[OK] The contributions are update ! \n ".$count.' / '.count($contributions);
    }
}
