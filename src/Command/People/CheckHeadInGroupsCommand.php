<?php

namespace App\Command\People;

use App\Repository\People\PeopleGroupRepository;
use App\Service\DoctrineTrait;
use App\Service\People\PeopleGroupChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour vérifier le demandeur principal dans chaque groupe et suivi.
 */
class CheckHeadInGroupsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:peopleGroup:check_head';

    protected $peopleGroupRepo;
    protected $manager;
    protected $peopleGroupChecker;

    public function __construct(PeopleGroupRepository $peopleGroupRepo, EntityManagerInterface $manager, PeopleGroupChecker $peopleGroupChecker)
    {
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->manager = $manager;
        $this->peopleGroupChecker = $peopleGroupChecker;
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
        $peopleGroups = $this->peopleGroupRepo->findBy([], ['updatedAt' => 'DESC'], 1000);
        $count = 0;

        foreach ($peopleGroups as $peopleGroup) {
            $countHeads = 0;
            foreach ($peopleGroup->getRolePeople() as $rolePerson) {
                if ($rolePerson->getHead()) {
                    ++$countHeads;
                }
            }
            if (1 != $countHeads) {
                echo $peopleGroup->getId()." => $countHeads DP\n";
                $this->peopleGroupChecker->checkValidHeader($peopleGroup);
                ++$count;
            }
        }

        $this->manager->flush();

        return "[OK] The headers in peopleGroup are checked !\n  ".$count.' / '.count($peopleGroups).' are invalids.';
    }
}
