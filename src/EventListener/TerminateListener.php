<?php

namespace App\EventListener;

use App\Entity\Organization\User;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Security\Core\Security;

class TerminateListener
{
    use DoctrineTrait;

    private $security;
    private $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->updateLastActivity();
    }

    /**
     * Met à jour la date de dernière activité de l'utilisateur connecté.
     */
    protected function updateLastActivity(): void
    {
        /** @var User */
        $user = $this->security->getUser();

        if ($this->em->isOpen() && $user && !$user->isActiveNow()) {
            $user->setLastActivityAt(new \DateTime());
            $this->disableListeners($this->em);
            $this->em->flush();
        }
    }
}
