<?php

namespace App\Command;

use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\EntityManager\PeopleGroupManager;
use Symfony\Component\Console\Command\Command;
use App\Repository\People\PeopleGroupRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour vérifier le demandeur principal dans chaque groupe et suivi.
 */
class CheckHeadInGroupsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:peopleGroup:check_head';

    protected $repo;
    protected $manager;
    protected $peopleGroupManager;

    public function __construct(PeopleGroupRepository $repo, EntityManagerInterface $manager, PeopleGroupManager $peopleGroupManager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->peopleGroupManager = $peopleGroupManager;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->checkHeadInGroups();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Mettre à jour le nb de personnes.
     */
    protected function checkHeadInGroups()
    {
        $count = 0;
        $peopleGroups = $this->repo->findAll();
        foreach ($peopleGroups as $peopleGroup) {
            $countHeads = 0;
            foreach ($peopleGroup->getRolePeople() as $rolePerson) {
                if ($rolePerson->getHead()) {
                    ++$countHeads;
                }
            }
            if ($countHeads != 1) {
                echo $peopleGroup->getId()." => $countHeads DP\n";
                $this->peopleGroupManager->checkValidHead($peopleGroup);
                ++$count;
            }
        }

        $this->manager->flush();

        return "[OK] The headers in peopleGroup are checked !\n  ".$count.' / '.count($peopleGroups).' are invalids.';
    }
}
