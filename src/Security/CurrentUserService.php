<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

class CurrentUserService
{
    protected $user;
    protected $session;

    public function __construct(Security $security, SessionInterface $session)
    {
        $this->user = $security->getUser();
        $this->session = $session;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getServices()
    {
        $services = [];

        foreach ($this->session->get('userServices') as $key => $value) {
            $services[] = $key;
        }

        return $services;
    }

    public function hasRole($role)
    {
        return in_array($role, $this->user->getRoles()) ? true : false;
    }
}
