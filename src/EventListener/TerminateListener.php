<?php

namespace App\EventListener;

use App\Entity\Organization\User;
use App\EntityManager\ExportManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Security\Core\Security;

class TerminateListener
{
    private $security;
    private $container;
    private $exportManager;

    public function __construct(
        Security $security,
        ContainerInterface $container,
        ExportManager $exportManager
    ) {
        $this->security = $security;
        $this->container = $container;
        $this->exportManager = $exportManager;
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        $this->updateLastActivity();

        switch ($route) {
            case 'export':
                $this->exportManager->export($request);
                break;
        }
    }

    /**
     * Met à jour la date de dernière activité de l'utilisateur connecté.
     */
    protected function updateLastActivity(): void
    {
        /** @var User */
        $user = $this->security->getUser();
        if ($user && !$user->isActiveNow()) {
            $user->setLastActivityAt(new \DateTime());
            $this->container->get('doctrine')->getManager()->flush();
        }
    }
}
