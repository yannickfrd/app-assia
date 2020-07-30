<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le nombre de personnes par suivi (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateNbPeopleBySupportCommand extends Command
{
    protected static $defaultName = 'app:support:update:nbPeople';

    protected $repo;
    protected $manager;

    public function __construct(SupportGroupRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateNbPeopleBySupport();
        $output->writeln("\e[32m".$message."\e[0m \n");

        return 0;
    }

    /**
     * Mettre à jour le nb de personnes.
     */
    protected function updateNbPeopleBySupport()
    {
        $count = 0;
        $supports = $this->repo->findAll();

        foreach ($supports as $support) {
            if (null == $support->getNbPeople()) {
                $support->setNbPeople($support->getSupportPeople()->count());
                ++$count;
            }
        }

        $this->manager->flush();

        return "[OK] Le nombre de personnes par suivi est mis à jour ! \n ".$count.' / '.count($supports);
    }
}
