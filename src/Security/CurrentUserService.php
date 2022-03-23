<?php

namespace App\Security;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use Symfony\Component\Security\Core\Security;

class CurrentUserService
{
    /** @var Security */
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getUser(): User
    {
        return $this->security->getUser();
    }

    public function getServices(): array
    {
        $services = [];

        foreach ($this->getUser()->getServices() as $service) {
            $services[] = $service->getId();
        }

        return $services;
    }

    public function isInService(Service $currentService): bool
    {
        foreach ($this->getServices() as $serviceId) {
            if ($serviceId === $currentService->getId()) {
                return true;
            }
        }

        return false;
    }

    public function hasRole(string $role): bool
    {
        return null != in_array($role, $this->getUser()->getRoles());
    }
}
