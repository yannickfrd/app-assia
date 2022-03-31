<?php

namespace App\Service\SupportGroup;

use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;

class SupportReferentSwitcher
{
    private $supportGroupRepo;
    private $em;

    public function __construct(
        SupportGroupRepository $supportGroupRepo,
        EntityManagerInterface $em
    ) {
        $this->supportGroupRepo = $supportGroupRepo;
        $this->em = $em;
    }

    public function switch(User $oldReferent, User $newReferent): int
    {
        $supports = $this->supportGroupRepo->findBy([
            'referent' => $oldReferent,
            'status' => SupportGroup::STATUS_IN_PROGRESS,
        ]);

        foreach ($supports as $supportGroup) {
            $supportGroup->setReferent($newReferent);

            SupportManager::deleteCacheItems($supportGroup, $oldReferent);
        }

        $this->em->flush();

        return count($supports);
    }
}
