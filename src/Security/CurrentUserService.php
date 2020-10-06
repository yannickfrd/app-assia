<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CurrentUserService
{
    protected $user;
    protected $session;

    public function __construct(Security $security, SessionInterface $session)
    {
        $this->user = $security->getUser();
        $this->session = $session;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getServices(): array
    {
        $services = [];

        foreach ($this->user->getServiceUser() as $role) {
            $services[] = $role->getService()->getId();
        }

        return $services;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->user->getRoles()) ? true : false;
    }
}
