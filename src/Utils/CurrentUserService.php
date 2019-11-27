<?php

namespace App\Utils;

use Symfony\Component\Security\Core\Security;

class CurrentUserService
{
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function getServices()
    {
        $services = [];

        foreach ($this->user->getroleUser() as $role) {
            $services[] = $role->getService()->getId();
        };

        return $services;
    }

    public function isAdmin($role)
    {
        foreach ($this->user->getRoles() as $value) {
            if ($value == $role) {
                return true;
            }
        }
        return false;
    }
}
