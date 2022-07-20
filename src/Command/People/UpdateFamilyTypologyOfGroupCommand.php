<?php

namespace App\Command\People;

use App\Entity\People\RolePerson;
use App\Repository\People\PeopleGroupRepository;
use App\Service\DoctrineTrait;
use App\Service\People\PeopleGroupManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:peopleGroup:update_family_typology',
    description: 'Update the family typology in people groups.',
)]
class UpdateFamilyTypologyOfGroupCommand extends Command
{
    use DoctrineTrait;

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
