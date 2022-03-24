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

    protected $em;
    protected $personRepo;

    public function __construct(EntityManagerInterface $em, PersonRepository $personRepo)
    {
        $this->em = $em;
        $this->personRepo = $personRepo;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $people = $this->personRepo->findBy(['gender' => 99]);
        $nbPeople = count($people);
        $count = 0;

        $io->createProgressBar();
        $io->progressStart($nbPeople);

        foreach ($people as $person) {
            $otherPerson = $this->personRepo->findOnePersonByFirstname($person->getFirstname());

            if ($otherPerson) {
                $person->setGender($otherPerson->getGender());
                ++$count;
            }

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();

        $io->success("The gender of people is update !\n  ".$count.' / '.count($people));

        return Command::SUCCESS;
    }
}
