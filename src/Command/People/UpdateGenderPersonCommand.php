<?php

namespace App\Command\People;

use App\Repository\People\PersonRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour mettre à jour le sexe des personnes si information non renseignée.
 */
class UpdateGenderPersonCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:person:update_gender';

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
        $io = new SymfonyStyle($input, $output);

        $people = $this->repo->findBy(['gender' => 99]);
        $count = 0;

        foreach ($people as $person) {
            $otherPerson = $this->repo->findOnePersonByFirstname($person->getFirstname());

            if ($otherPerson) {
                $person->setGender($otherPerson->getGender());
                ++$count;
            }
        }

        $this->manager->flush();

        $io->success("The gender of people is update !\n  ".$count.' / '.count($people));

        return Command::SUCCESS;
    }
}
