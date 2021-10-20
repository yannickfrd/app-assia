<?php

namespace App\Command\User;

use App\Repository\Organization\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Commande pour réinitialiser les mot de passe des utilisateurs (uniquement en mode développement).
 */
class ReinitPasswordUserCommand extends Command
{
    protected static $defaultName = 'app:user:reinit_password';
    protected static $defaultDescription = 'Reinit password users in development environnement.';

    protected $manager;
    protected $userRepo;
    protected $encoder;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepo, UserPasswordEncoderInterface $encoder)
    {
        $this->manager = $manager;
        $this->userRepo = $userRepo;
        $this->encoder = $encoder;

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
            $io->error('Environnement invalid ');

            return Command::FAILURE;
        }

        $nbUsers = 0;
        $count = 0;
        $users = $this->userRepo->findBy(['disabledAt' => null]);

        foreach ($users as $user) {
            if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $user->setPassword($this->encoder->encodePassword($user, 'test'));
                ++$count;
            }
            ++$nbUsers;
        }
        $this->manager->flush();

        $io->success("Reinit password users is successfull !\n  ".$count.' / '.$nbUsers);

        return Command::SUCCESS;
    }
}
