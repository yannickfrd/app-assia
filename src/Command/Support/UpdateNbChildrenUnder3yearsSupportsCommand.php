<?php

namespace App\Command\Support;

use App\Command\CommandTrait;
use App\Entity\People\RolePerson;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le nombre d'enfants de moins de 3 ans.
 */
class UpdateNbChildrenUnder3yearsSupportsCommand extends Command
{
    use CommandTrait;

    protected static $defaultName = 'app:support:update_nb_children_under_3_years';

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
        $this->update();

        return Command::SUCCESS;
    }

    protected function update()
    {
        $today = new \DateTime();

        $count = 0;
        $supports = $this->repo->findAll();
        foreach ($supports as $supportGroup) {
            $nbChildren = 0;
            $nbChildrenUnder3years = 0;
            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if (RolePerson::ROLE_CHILD === $supportPerson->getRole()) {
                    ++$nbChildren;
                }
                $birthdate = $supportPerson->getPerson()->getBirthdate();
                $age = $birthdate->diff($supportPerson->getEndDate() ?? $today)->y ?? 0;
                if ($age < 3) {
                    ++$nbChildrenUnder3years;
                    ++$count;
                }
            }
            if ($nbChildren > 0) {
                $supportGroup->setNbChildrenUnder3years($nbChildrenUnder3years);
            }
        }
        $this->manager->flush();

        $this->writeMessage('success', "The number of children under 3 years in supports are update ! \n ".$count.' / '.count($supports));
    }
}
