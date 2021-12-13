<?php

namespace App\Command\User;

use App\Repository\Organization\UserRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour changer le nom des utilisateurs (uniquement en mode développement).
 */
class RenameUserCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:user:rename';
    protected static $defaultDescription = 'Rename users in development environnement.';

    protected $em;
    protected $userRepo;
    protected $faker;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepo)
    {
        $this->em = $em;
        $this->userRepo = $userRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
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

        $users = $this->userRepo->findBy(['disabledAt' => null]);
        $nbUsers = count($users);

        $io->createProgressBar();
        $io->progressStart($nbUsers);

        if ('dev' != $_SERVER['APP_ENV'] || 'localhost' != $_SERVER['DB_HOST']) {
            $io->error('Environnement invalid');

            return Command::FAILURE;
        }

        foreach ($users as $user) {
            $user->setLastname($this->faker->lastName)
                ->setFirstname($this->faker->firstName());

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();

        $io->success('Change name of people is successfull !'
            ."\n  ".$nbUsers.' users modified.');

        return Command::SUCCESS;
    }
}
