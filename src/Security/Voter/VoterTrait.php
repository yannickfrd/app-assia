<?php

namespace App\Security\Voter;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;

trait VoterTrait
{
    /** @var User */
    protected $user;

    protected function isGranted(string $role): bool
    {
        return in_array($role, $this->user->getRoles());
    }

    /**
     * If current user is in the service.
     */
    protected function isUserOfService(Service $service): bool
    {
        $serviceId = $service->getId();
        foreach ($this->user->getServices() as $service) {
            if ($serviceId === $service->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * If current user is administrator of service.
     */
    protected function isAdminOfService(Service $service): bool
    {
        $serviceId = $service->getId();
        if ($this->isGranted('ROLE_ADMIN')) {
            foreach ($this->user->getServices() as $service) {
                if ($serviceId === $service->getId()) {
                    return true;
                }
            }
        }

        return false;
    }
}
