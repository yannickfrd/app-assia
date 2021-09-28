<?php

namespace App\Command\User;

use App\Repository\Organization\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Commande pour réinitialiser les mot de passe des utilisateurs (uniquement en mode développement).
 */
class ReinitPasswordUserCommand extends Command
{
    protected static $defaultName = 'app:user:reinit_password';

    protected $manager;
    protected $repo;
    protected $encoder;

    public function __construct(EntityManagerInterface $manager, UserRepository $repo, UserPasswordEncoderInterface $encoder)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->encoder = $encoder;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Reinit password users in development environnement.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ('dev' != $_SERVER['APP_ENV'] || 'localhost' != $_SERVER['DB_HOST']) {
            $output->writeln("\e[97m\e[41m\n Environnement invalid \e[0m\n");

            return Command::FAILURE;
        }

        $nbUsers = 0;
        $count = 0;
        $users = $this->repo->findBy(['disabledAt' => null]);

        foreach ($users as $user) {
            if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $user->setPassword($this->encoder->encodePassword($user, 'test'));
                ++$count;
            }
            ++$nbUsers;
        }
        $this->manager->flush();

        $message = "[OK] Reinit password users is successfull !\n  ".$count.' / '.$nbUsers;
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }
}
