<?php

namespace App\Command;

use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le nombre de personnes par suivi (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateNbPeopleBySupportCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:update:nbPeople';

    protected $repo;
    protected $manager;

    public function __construct(SupportGroupRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateNbPeopleBySupport();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Mettre à jour le nb de personnes.
     */
    protected function updateNbPeopleBySupport()
    {
        $supports = $this->repo->findBy([], ['updatedAt' => 'DESC'], 1000);
        $count = 0;

        foreach ($supports as $support) {
            $nbSupportPeople = 0;
            foreach ($support->getSupportPeople() as $supportPerson) {
                if (null === $support->getEndDate() && null === $supportPerson->getEndDate()) {
                    ++$nbSupportPeople;
                }
            }
            if ($support->getNbPeople() != $nbSupportPeople) {
                $support->setNbPeople($nbSupportPeople);
                ++$count;
            }
        }

        $this->manager->flush();

        return "[OK] The number of people by support is update !\n  ".$count.' / '.count($supports);
    }
}
