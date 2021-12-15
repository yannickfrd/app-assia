<?php

namespace App\Command\People;

use App\Repository\People\PeopleGroupRepository;
use App\Repository\People\PersonRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Commande pour changer le nom des personnes (uniquement en mode développement).
 */
class RenamePeopleCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:person:rename';
    protected static $defaultDescription = 'Change the name of people in development environnement.';

    protected $em;
    protected $peopleGroupRepo;
    protected $personRepo;
    protected $faker;
    protected $stopwatch;

    public function __construct(
        EntityManagerInterface $em,
        PeopleGroupRepository $peopleGroupRepo,
        PersonRepository $personRepo,
        Stopwatch $stopwatch
    ) {
        $this->em = $em;
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->personRepo = $personRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
        $this->stopwatch = $stopwatch;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ('dev' != $_SERVER['APP_ENV'] || 'localhost' != $_SERVER['DB_HOST']) {
            $io->error('Environnement invalid');

            return Command::FAILURE;
        }

        $this->stopwatch->start('command');

        $nbPeople = $this->personRepo->count([]);

        $io->createProgressBar();
        $io->progressStart($nbPeople);

        $peopleGroups = $this->peopleGroupRepo->findAll(['status' => 2]);

        foreach ($peopleGroups  as $group) {
            $lastname = $this->faker->lastName;
            foreach ($group->getPeople() as $person) {
                $birthdate = $person->getBirthdate();

                $person->setLastname($lastname)
                    ->setFirstname($this->faker->firstName(1 === $person->getGender() ? 'female' : 'male'))
                    ->setBirthdate($this->faker->dateTimeBetween($birthdate, (clone $birthdate)->modify('+ 3 month')));

                $io->progressAdvance();
            }
        }

        $this->em->flush();

        $io->progressFinish();

        $this->stopwatch->stop('command');

        $io->success('Change name of people is successfull !'
            ."\n  ".$nbPeople.' people modified.'
            ."\n  ".number_format($this->stopwatch->start('command')->getDuration(), 0, ',', ' ').' ms');

        return Command::SUCCESS;
    }
}
