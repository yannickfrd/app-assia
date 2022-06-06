<?php

namespace App\Command\People;

use App\Repository\People\PersonRepository;
use App\Service\DoctrineTrait;
use App\Service\SoundexFr;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:person:update_soundex_name',
    description: 'Update the soundex name of people.',
)]
class UpdateSoundexNamePersonCommand extends Command
{
    use DoctrineTrait;

    protected $em;
    protected $personRepo;
    protected $soundexFr;

    public function __construct(EntityManagerInterface $em, PersonRepository $personRepo, SoundexFr $soundexFr)
    {
        $this->em = $em;
        $this->personRepo = $personRepo;
        $this->soundexFr = $soundexFr;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');

        $people = $this->personRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $nbPeople = count($people);

        $io->createProgressBar();
        $io->progressStart($nbPeople);

        foreach ($people as $person) {
            $person->setSoundexFirstname($this->soundexFr->get2($person->getFirstname()));
            $person->setSoundexLastname($this->soundexFr->get2($person->getLastname()));

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();

        $io->success("The soundex names of people are update !\n  ".$nbPeople);

        return Command::SUCCESS;
    }
}
