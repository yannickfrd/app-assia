<?php

namespace App\Command\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use App\Repository\Organization\UserRepository;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Commande pour réinitialiser les mot de passe des utilisateurs (uniquement en mode développement).
 */
class ReinitPasswordUserCommand extends Command
{
    protected static $defaultName = 'app:user:reinit_password';
    protected static $defaultDescription = 'Reinit password users in development environnement.';

    protected $em;
    protected $userRepo;
    protected $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepo, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->userRepo = $userRepo;
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
            $io->error('Environnement invalid ');

            return Command::FAILURE;
        }

        $nbUsers = 0;
        $count = 0;
        $users = $this->userRepo->findBy(['disabledAt' => null]);

        foreach ($users as $user) {
            if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $user->setPassword($this->passwordHasher->hashPassword($user, 'test'));
                ++$count;
            }
            ++$nbUsers;
        }
        $this->em->flush();

        $io->success("Reinit password users is successfull !\n  ".$count.' / '.$nbUsers);

        return Command::SUCCESS;
    }
}
