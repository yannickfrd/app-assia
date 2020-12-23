<?php

namespace App\Command;

use App\Repository\People\PersonRepository;
use App\Service\SoundexFr;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le nom Soundex des personnes.
 */
class UpdateSoundexNamePersonCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:person:update:soundex_name';

    protected $manager;
    protected $repo;
    protected $soundexFr;

    public function __construct(EntityManagerInterface $manager, PersonRepository $repo, SoundexFr $soundexFr)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->soundexFr = $soundexFr;
        $this->disableListeners();

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateSoundexName();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    protected function updateSoundexName()
    {
        $count = 0;
        $people = $this->repo->findAll();

        foreach ($people as $person) {
            $person->setSoundexFirstname($this->soundexFr->get2($person->getFirstname()));
            $person->setSoundexLastname($this->soundexFr->get2($person->getLastname()));
            ++$count;
        }

        $this->manager->flush();

        return "[OK] The soundex names of people are update !\n  ".$count.' / '.count($people);
    }
}
