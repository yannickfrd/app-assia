<?php

namespace App\Command\User;

use App\Entity\Organization\User;
use App\Repository\Organization\UserRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    protected $slugger;
    protected $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepo,
        SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->userRepo = $userRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
        $this->slugger = $slugger;
        $this->passwordHasher = $passwordHasher;

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
            $io->error('Environnement is invalid!');

            return Command::FAILURE;
        }

        $users = $this->userRepo->findBy(['disabledAt' => null]);

        $io->createProgressBar();
        $io->progressStart(count($users));

        $count = 0;

        $hashedPassword = $this->passwordHasher->hashPassword(new User(), 'test');

        foreach ($users as $user) {
            $firstname = $this->faker->firstName();
            $lastname = $this->faker->lastName();
            $username = strtolower(substr($this->slugger->slug($firstname), 0, 1).'.'.$this->slugger->slug($lastname));

            $user
                ->setFirstName($firstname)
                ->setLastName($lastname)
                ->setEmail($username.'@app-assia.org')
            ;

            if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $user
                    ->setUsername($username)
                    ->setPassword($hashedPassword)
                ;
            }

            ++$count;

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();

        $io->success("$count users modified !");

        return Command::SUCCESS;
    }
}
