<?php

namespace App\Command;

use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\People\PersonRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le sexe des personnes si information non renseignée.
 */
class UpdateGenderPersonCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:person:update:gender';

    protected $manager;
    protected $repo;

    public function __construct(EntityManagerInterface $manager, PersonRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateGender();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Mettre à jour le sexe.
     */
    protected function updateGender()
    {
        $count = 0;
        $people = $this->repo->findBy(['gender' => 99]);

        foreach ($people as $person) {
            $otherPerson = $this->repo->findOnePersonByFirstname($person->getFirstname());

            if ($otherPerson) {
                $person->setGender($otherPerson->getGender());
                ++$count;
            }
        }

        $this->manager->flush();

        return "[OK] The gender of people is update !\n  ".$count.' / '.count($people);
    }
}
