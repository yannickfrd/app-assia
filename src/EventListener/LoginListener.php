<?php

namespace App\EventListener;

use App\Entity\UserConnection;
use App\Repository\UserConnectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $manager;
    private $session;

    public function __construct(EntityManagerInterface $manager, SessionInterface $session, UserConnectionRepository $repo)
    {
        $this->session = $session;
        $this->repo = $repo;
        $this->manager = $manager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $lastConnection = $this->repo->findOneBy(
            ['user' => $user],
            ['connectionAt' => 'DESC']
        );

        if ($lastConnection) {
            $user->setLastLogin($lastConnection->getConnectionAt());
        } else {
            $user->setLastLogin(new \DateTime());
        }

        $user->setLogincount($user->getLogincount() + 1);
        $user->setFailureLogincount(0);

        $connection = new UserConnection();

        $connection->setConnectionAt(new \DateTime())
            ->setUser($user);

        $this->manager->persist($connection);
        $this->manager->flush();

        // Récupère en session les services rattachés à l'utilisateur et le code couleur du 1er service
        $servicesUser = [];
        $i = 0;

        foreach ($user->getServiceUser() as $serviceUser) {
            if (0 == $i && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $this->session->set('themeColor', $serviceUser->getService()->getPole()->getColor());
            }
            $servicesUser[] = $serviceUser->getService()->getName();
            ++$i;
        }

        $this->session->set('servicesUser', $servicesUser);
    }

    public function onSecurityAuthentificationFailure(AuthenticationFailureEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $count = $user->getFailureLogincount() + 1;
        $user->setFailureLogincount($count);
        $this->manager->flush();
    }
}
