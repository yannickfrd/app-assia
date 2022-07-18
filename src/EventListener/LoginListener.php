<?php

namespace App\EventListener;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\Organization\UserConnection;
use App\Form\Utils\Choices;
use App\Repository\Organization\UserConnectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginListener
{
    private $em;
    private $session;
    private $userConnectionRepo;
    private $translator;

    public function __construct(
        EntityManagerInterface $em,
        UserConnectionRepository $userConnectionRepo,
        TranslatorInterface $translator
    ) {
        $this->session = new Session();
        $this->userConnectionRepo = $userConnectionRepo;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
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
        $lastConnection = $this->userConnectionRepo->findOneBy(
            ['user' => $user],
            ['connectionAt' => 'DESC']
        );

        $lastConnection ? $user->setLastLogin($lastConnection->getConnectionAt()) : $user->setLastLogin(new \DateTime());

        $user->setLogincount($user->getLogincount() + 1)
            ->setFailureLogincount(0);

        $connection = (new UserConnection())
            ->setConnectionAt(new \DateTime())
            ->setUser($user);

        try {
            if ($this->em->isOpen()) {
                $this->em->persist($connection);
                $this->em->flush();
            }
        } catch (\Exception $e) {
            // throw $e;
        }
    }

    /**
     * Récupère en session le code couleur du 1er service.
     */
    private function addColorServiceInSession(User $user): void
    {
        if ($user->getServices()->count() > 0 && null !== $user->getServices()->first()->getPole()) {
            $this->session->set('theme_color', $user->getServices()->first()->getPole()->getColor());
        }
    }

    /**
     * Récupère en session les services rattachés à l'utilisateur.
     */
    private function addUserServicesInSession(User $user): void
    {
        $services = [];
        foreach ($user->getServices() as $service) {
            $services[$service->getId()] = $service->getName();
            if (Choices::YES === $service->getPlace()) {
                $haveServiceWithPlace = true;
            }
            if (Service::SERVICE_TYPE_AVDL === $service->getType()) {
                $haveServiceAVDL = true;
            }
            if (Service::SERVICE_TYPE_HOTEL === $service->getType()) {
                $haveServiceHotel = true;
            }
        }

        $this->session->set('userServices', $services);
        $this->session->set('haveServiceWithPlace', $haveServiceWithPlace ?? false);
        $this->session->set('haveServiceAVDL', $haveServiceAVDL ?? false);
        $this->session->set('haveServiceHotel', $haveServiceHotel ?? false);
    }

    private function addFlashMessages(User $user): void
    {
        $flashBag = $this->session->getFlashBag();

        $flashBag->add('success', $this->translator->trans('login.greeting', [
            'firstname' => $user->getFirstname(),
        ], 'app'));

        if (!$user->getPhone1()) {
            $flashBag->add('warning', 'login.alert_no_phone');
        }
    }
}
