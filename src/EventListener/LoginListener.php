<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\UserConnection;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserConnectionRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class LoginListener
{
    private $entityManager;
    private $session;

    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session, UserConnectionRepository $repo)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->repo = $repo;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $lastConnection = $this->repo->findOneBy(
            ["user" => $user],
            ["connectionAt" => "DESC"]
        );

        if ($lastConnection) {
            $user->setLastLogin($lastConnection->getConnectionAt());
        } else {
            $user->setLastLogin(new \DateTime());
        }

        $count = $user->getLogincount();
        $count++;
        $user->setLogincount($count);
        $user->setFailureLogincount(0);

        $connection = new UserConnection();
        $connection->setConnectionAt(new \DateTime())
            ->setUser($user);

        $this->entityManager->persist($connection);
        $this->entityManager->flush();

        // Récupère en session les services rattachés à l'utilisateur et le code couleur du 1er service
        $servicesUser = [];
        $i = 0;

        foreach ($user->getServiceUser() as $serviceUser) {
            if ($i == 0) {
                $this->session->set("themeColor", $serviceUser->getService()->getPole()->getColor());
            }
            $servicesUser[] = $serviceUser->getService()->getName();
            $i++;
        }

        $this->session->set("servicesUser", $servicesUser);

        $this->session->getFlashBag()->add("success", "Bonjour " .  $user->getFirstname() . " !");
    }

    public function onSecurityAuthentificationFailure(AuthenticationFailureEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $count = $user->getFailureLogincount();
        $count++;
        $user->setFailureLogincount($count);

        $this->session->getFlashBag()->add("danger", "Identifiant ou mot de passe incorrect.");
    }
}
