<?php

namespace App\Security;

use App\Entity\Organization\User;
use Symfony\Component\Security\Core\Security;

class CurrentUserService
{
    protected $user;

    public function __construct(Security $security)
    {
        /** @var User */
        $this->user = $security->getUser();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getServices(): array
    {
        $services = [];

        foreach ($this->user->getServices() as $service) {
            $services[] = $service->getId();
        }

        return $services;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->user->getRoles()) != null;
    }
}
