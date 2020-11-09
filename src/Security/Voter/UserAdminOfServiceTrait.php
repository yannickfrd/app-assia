<?php

namespace App\Security\Voter;

use App\Entity\Service;

trait UserAdminOfServiceTrait
{
    /**
     * If current user is administrator of service.
     */
    protected function isAdminUserOfService(Service $service): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            foreach ($this->currentUser->getServiceUser() as $serviceCurrentUser) {
                if ($service->getId() === $serviceCurrentUser->getService()->getId()) {
                    return true;
                }
            }
        }

        return false;
    }
}
