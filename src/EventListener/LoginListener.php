<?php

namespace App\EventListener;

use App\Entity\Organization\User;
use App\Entity\Organization\UserConnection;
use App\Form\Utils\Choices;
use App\Repository\Organization\UserConnectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $manager;
    private $session;
    private $repo;

    public function __construct(EntityManagerInterface $manager, SessionInterface $session, UserConnectionRepository $repo)
    {
        $this->session = $session;
        $this->repo = $repo;
        $this->manager = $manager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /** @var User */
        $user = $event->getAuthenticationToken()->getUser();

        $lastConnection = $this->repo->findOneBy(
            ['user' => $user],
            ['connectionAt' => 'DESC']
        );

        $lastConnection ? $user->setLastLogin($lastConnection->getConnectionAt()) : $user->setLastLogin(new \DateTime());

        $user->setLogincount($user->getLogincount() + 1)
            ->setFailureLogincount(0);

        $connection = (new UserConnection())
            ->setConnectionAt(new \DateTime())
            ->setUser($user);

        $this->manager->persist($connection);
        $this->manager->flush();

        // Récupère en session le code couleur du 1er service
        if (count($user->getServiceUser()) > 0) {
            $this->session->set('theme_color', $user->getServiceUser()[0]->getService()->getPole()->getColor());
        }

        // Récupère en session les services rattachés à l'utilisateur
        $userServices = [];
        $haveServiceWithAccommodation = false;
        foreach ($user->getServiceUser() as $serviceUser) {
            $service = $serviceUser->getService();
            $userServices[$service->getId()] = $service->getName();
            if ($service->getAccommodation() == Choices::YES) {
                $haveServiceWithAccommodation = true;
            }
        }
        $this->session->set('userServices', $userServices);
        $this->session->set('haveServiceWithAccommodation', $haveServiceWithAccommodation);
    }

    // public function onSecurityAuthentificationFailure(AuthenticationFailureEvent $event)
    // {
    //     /** @var User */
    //     $user = $event->getAuthenticationToken()->getUser();

    //     $user->setFailureLogincount($user->getFailureLogincount() + 1);

    //     $this->manager->flush();
    // }
}
