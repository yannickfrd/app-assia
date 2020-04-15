<?php

namespace App\Security\Voter;

use App\Entity\User;

trait UserIsAdminTrait
{
    // If current user is administrator
    protected function userIsAdmin(User $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            foreach ($this->currentUser->getServiceUser() as $serviceCurrentUser) {
                foreach ($user->getServiceUser() as $serviceUser) {
                    if ($serviceUser->getService()->getId() == $serviceCurrentUser->getService()->getId()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
