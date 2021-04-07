<?php

namespace App\EventListener;

use App\Entity\Organization\User;
use App\Event\Support\SupportPersonExportEvent;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Security\Core\Security;

class TerminateListener
{
    use DoctrineTrait;

    private $security;
    private $manager;
    private $dispatcher;

    public function __construct(Security $security, EntityManagerInterface $manager, EventDispatcherInterface $dispatcher)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->updateLastActivity();

        $request = $event->getRequest();
        $route = $request->get('_route');
        if ('export' === $route) {
            $this->dispatcher->dispatch(new SupportPersonExportEvent($request), 'support_person.full_export');
        }
    }

    /**
     * Met à jour la date de dernière activité de l'utilisateur connecté.
     */
    protected function updateLastActivity(): void
    {
        /** @var User */
        $user = $this->security->getUser();

        if ($this->manager->isOpen() && $user && !$user->isActiveNow()) {
            $user->setLastActivityAt(new \DateTime());
            $this->disableListeners($this->manager);
            $this->manager->flush();
        }
    }
}
