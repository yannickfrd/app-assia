<?php

namespace App\Command\People;

use App\Repository\Organization\UserRepository;
use App\Repository\People\PeopleGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Commande pour changer le nom des personnes et des utilisateurs (uniquement en mode développement).
 */
class ChangeNamePeopleCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:person:change_name';
    protected static $defaultDescription = 'Change the name of people in development environnement.';

    protected $manager;
    protected $userRepo;
    protected $peopleGroupRepo;
    protected $faker;
    protected $stopwatch;

    public function __construct(
        EntityManagerInterface $manager,
        UserRepository $userRepo,
        PeopleGroupRepository $peopleGroupRepo,
        Stopwatch $stopwatch
    ) {
        $this->manager = $manager;
        $this->userRepo = $userRepo;
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
        $this->stopwatch = $stopwatch;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if ('dev' != $_SERVER['APP_ENV'] || 'localhost' != $_SERVER['DB_HOST']) {
            $io->error('Environnement invalid');

            return Command::FAILURE;
        }

        $this->stopwatch->start('command');

        foreach ($this->userRepo->findBy(['disabledAt' => null]) as $user) {
            $user->setLastname($this->faker->lastName)
                ->setFirstname($this->faker->firstName());
        }

        $nbPeople = 0;
        $peopleGroups = $this->peopleGroupRepo->findAll(['status' => 2]);

        foreach ($peopleGroups  as $group) {
            $lastname = $this->faker->lastName;
            foreach ($group->getPeople() as $person) {
                $birthdate = $person->getBirthdate();
                $person->setLastname($lastname)
                    ->setFirstname($this->faker->firstName(1 === $person->getGender() ? 'female' : 'male'))
                    ->setBirthdate($this->faker->dateTimeBetween($birthdate, (clone $birthdate)->modify('+ 3 month')));
                ++$nbPeople;
            }
        }

        $this->manager->flush();

        $this->stopwatch->stop('command');

        $io->success('Change name of people is successfull !'
            ."\n  ".$nbPeople.' people modified.'
            ."\n  ".number_format($this->stopwatch->start('command')->getDuration(), 0, ',', ' ').' ms');

        return Command::SUCCESS;
    }
}
