<?php

namespace App\Command\Support;

use App\Entity\People\RolePerson;
use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le nombre de personnes par suivi (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateFamilyTypologyOfSupportCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:update_family_typology';

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
        $message = $this->update();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }

    protected function update()
    {
        $supports = $this->repo->findBy([], ['updatedAt' => 'DESC']);
        $count = 0;

        foreach ($supports as $supportGroup) {
            $peopleGroup = $supportGroup->getPeopleGroup();
            $nbSupportPeople = 0;

            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                $person = $supportPerson->getPerson();

                if (null === $supportGroup->getEndDate() && null === $supportPerson->getEndDate()) {
                    ++$nbSupportPeople;
                }
                if (in_array($peopleGroup->getFamilyTypology(), [1, 2]) && 1 === $supportGroup->getNbPeople()
                    && 5 != $supportPerson->getRole()) {
                    $supportPerson->setRole(5);
                    ++$count;
                }
                if (in_array($peopleGroup->getFamilyTypology(), [4, 5]) && $supportGroup->getNbPeople() > 1
                    && true === $supportPerson->getHead() && 4 != $supportPerson->getRole()) {
                    $supportPerson->setRole(4);
                    ++$count;
                }
                if ($person->getAge() <= 16 && RolePerson::ROLE_CHILD != $supportPerson->getRole()) {
                    $supportPerson->setRole(RolePerson::ROLE_CHILD);
                    ++$count;
                }
            }
            if ($supportGroup->getNbPeople() != $nbSupportPeople) {
                $supportGroup->setNbPeople($nbSupportPeople);
                ++$count;
            }
        }

        $this->manager->flush();

        return "[OK] The typology family of supports is update !\n  ".$count.' / '.count($supports);
    }
}
