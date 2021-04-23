<?php

namespace App\Command;

use App\Entity\People\RolePerson;
use App\Service\People\PeopleGroupManager;
use App\Repository\People\PeopleGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour la typologie familiale des groupes de personnes.
 */
class UpdateFamilyTypologyOfGroupCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:peopleGroup:update:family_typology';

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
        $message = $this->update();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    protected function update()
    {
        $peopleGroups = $this->repo->findBy([], ['updatedAt' => 'DESC'], 5000);
        $count = 0;

        foreach ($peopleGroups as $peopleGroup) {
            $nbRolePeople = $peopleGroup->getRolePeople()->count();
            if ($nbRolePeople != $peopleGroup->getNbPeople() && $nbRolePeople > 1) {
                $peopleGroup->setNbPeople($nbRolePeople);
                ++$count;
            }
            foreach ($peopleGroup->getRolePeople() as $rolePerson) {
                $person = $rolePerson->getPerson();
                if (1 === $peopleGroup->getFamilyTypology() && $nbRolePeople > 1) {
                    $peopleGroup->setFamilyTypology(4);
                    $peopleGroup->setNbPeople($nbRolePeople);
                    ++$count;
                }
                if (2 === $peopleGroup->getFamilyTypology() && $nbRolePeople > 1) {
                    $peopleGroup->setFamilyTypology(5);
                    $peopleGroup->setNbPeople($nbRolePeople);
                    ++$count;
                }
                if (3 === $peopleGroup->getFamilyTypology() && $nbRolePeople > 2) {
                    $peopleGroup->setFamilyTypology(6);
                    $peopleGroup->setNbPeople($nbRolePeople);
                    ++$count;
                }
                if (in_array($peopleGroup->getFamilyTypology(), [1, 2]) && 1 === $peopleGroup->getNbPeople()
                    && 5 != $rolePerson->getRole()) {
                    $rolePerson->setRole(5);
                    ++$count;
                }
                if (in_array($peopleGroup->getFamilyTypology(), [4, 5]) && $peopleGroup->getNbPeople() > 1
                    && true === $rolePerson->getHead() && 4 != $rolePerson->getRole()) {
                    $rolePerson->setRole(4);
                    ++$count;
                }
                if ($person->getAge() <= 16 && RolePerson::ROLE_CHILD != $rolePerson->getRole()) {
                    $rolePerson->setRole(RolePerson::ROLE_CHILD);
                    ++$count;
                }
            }
        }

        $this->manager->flush();

        return "[OK] The typology family of peopleGroup are updated !\n  ".$count.' / '.count($peopleGroups);
    }
}
