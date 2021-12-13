<?php

namespace App\Command\People;

use App\Service\DoctrineTrait;
use App\Entity\People\RolePerson;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\People\PeopleGroupManager;
use Symfony\Component\Console\Command\Command;
use App\Repository\People\PeopleGroupRepository;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour la typologie familiale des groupes de personnes.
 */
class UpdateFamilyTypologyOfGroupCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:peopleGroup:update_family_typology';
    protected static $defaultDescription = 'Update the family typology in groups';

    protected $peopleGroupRepo;
    protected $em;
    protected $peopleGroupManager;

    public function __construct(PeopleGroupRepository $peopleGroupRepo, EntityManagerInterface $em, PeopleGroupManager $peopleGroupManager)
    {
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->em = $em;
        $this->peopleGroupManager = $peopleGroupManager;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('fix', InputArgument::OPTIONAL, 'Fix the problem')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');
        $arg = $input->getArgument('fix');

        $peopleGroups = $this->peopleGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $nbPeopleGroups = count($peopleGroups);
        $count = 0;

        $io->createProgressBar();
        $io->progressStart($nbPeopleGroups);

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
            
            $io->progressAdvance();
        }

        if ('fix' === $arg) {
            $this->em->flush();
        }

        $io->progressFinish();

        $io->success("The typology family of peopleGroup are updated !\n  ".$count.' / '.$nbPeopleGroups);

        return Command::SUCCESS;
    }
}
