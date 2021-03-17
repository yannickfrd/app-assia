<?php

namespace App\EventListener;

use App\Entity\Organization\User;
use App\Entity\Organization\UserConnection;
use App\Form\Utils\Choices;
use App\Repository\Organization\UserConnectionRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    use DoctrineTrait;

    private $manager;
    /** @var Session */
    private $session;
    private $repo;

    public function __construct(EntityManagerInterface $manager, SessionInterface $session, UserConnectionRepository $repo)
    {
        $this->session = $session;
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /** @var User */
        $user = $event->getAuthenticationToken()->getUser();

        $this->addLastConnection($user);
        $this->addColorServiceInSession($user);
        $this->addUserServicesInSession($user);
        $this->addFlashMessages($user);
    }

    private function addLastConnection(User $user): void
    {
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

        if ($this->manager->isOpen()) {
            $this->manager->persist($connection);
            $this->manager->flush();
        }
    }

    /**
     * Récupère en session le code couleur du 1er service.
     */
    private function addColorServiceInSession(User $user): void
    {
        if ($user->getServiceUser()->count() > 0) {
            $this->session->set('theme_color', $user->getServiceUser()->first()->getService()->getPole()->getColor());
        }
    }

    /**
     * Récupère en session les services rattachés à l'utilisateur.
     */
    private function addUserServicesInSession(User $user): void
    {
        $userServices = [];
        $haveServiceWithPlace = false;

        foreach ($user->getServiceUser() as $serviceUser) {
            $service = $serviceUser->getService();
            $userServices[$service->getId()] = $service->getName();
            if (Choices::YES === $service->getPlace()) {
                $haveServiceWithPlace = true;
            }
        }

        $this->session->set('userServices', $userServices);
        $this->session->set('haveServiceWithPlace', $haveServiceWithPlace);
    }

    private function addFlashMessages(User $user): void
    {
        $flashBag = $this->session->getFlashBag();

        $flashBag->add('success', "Bonjour {$user->getFirstname()} !");

        if (!$user->getPhone1()) {
            $flashBag->add('warning', "Attention, votre numéro de téléphone n'est pas renseigné. Cliquez sur votre prénom en haut à droite pour l'ajouter.");
        }
    }
}
