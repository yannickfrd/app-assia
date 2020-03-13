<?php

namespace App\Security;

use Symfony\Component\Security\Core\Security;

class CurrentUserService
{
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getServices()
    {
        $services = [];

        foreach ($this->user->getServiceUser() as $role) {
            $services[] = $role->getService()->getId();
        };

        return $services;
    }

    public function isRole($role)
    {
        foreach ($this->user->getRoles() as $value) {
            if ($value == $role) {
                return true;
            }
        }
        return false;
    }
}
