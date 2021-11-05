<?php

namespace App\Service\SupportGroup;

use App\Entity\Organization\Device;
use App\Entity\Support\Avdl;
use App\Entity\Support\SupportGroup;

class AvdlService
{
    /**
     * Met à jour le suivi social d'un AVDL.
     */
    public function updateSupportGroup(SupportGroup $supportGroup): SupportGroup
    {
        $avdl = $supportGroup->getAvdl();

        if (null === $avdl) {
            return $supportGroup;
        }

        $supportGroup
            ->setStatus($this->getAvdlStatus($avdl))
            ->setStartDate($this->getAvdlStartDate($avdl))
            ->setEndDate($this->getAvdlEndDate($avdl));

        if (Device::AVDL_DALO === $supportGroup->getDevice()->getCode()) {
            $supportGroup->setCoefficient($this->getAvdlCoeffSupport($avdl));
        }

        $this->updateSupportPeople($supportGroup);

        return $supportGroup;
    }

    /**
     * Met à jour les suivis individuelles des personnes en AVDL.
     */
    protected function updateSupportPeople(SupportGroup $supportGroup): void
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if (null === $supportPerson->getEndStatus()) {
                $supportPerson
                    ->setStatus($supportGroup->getStatus())
                    ->setStartDate($supportGroup->getStartDate())
                    ->setEndDate($supportGroup->getEndDate());
            }
        }
    }

    /**
     * Donne le statut du suivi social en AVDL.
     */
    protected function getAvdlStatus(Avdl $avdl): int
    {
        // if (null === $avdl->getDiagStartDate() && null === $avdl->getSupportStartDate()) {
        //     return SupportGroup::STATUS_PRE_ADD_IN_PROGRESS;
        // }

        if (($avdl->getDiagEndDate() && null === $avdl->getSupportStartDate()) || $avdl->getSupportEndDate()) {
            return SupportGroup::STATUS_ENDED;
        }

        return SupportGroup::STATUS_IN_PROGRESS;
    }

    /**
     * Donne la date de début du suivi AVDL.
     */
    protected function getAvdlStartDate(Avdl $avdl): ?\DateTimeInterface
    {
        if ($avdl->getDiagStartDate()) {
            return $avdl->getDiagStartDate();
        }

        return $avdl->getSupportStartDate();
    }

    /**
     * Donne la date de fin du suivi AVDL.
     */
    protected function getAvdlEndDate(Avdl $avdl): ?\DateTimeInterface
    {
        if ($avdl->getSupportEndDate() || ($avdl->getDiagEndDate() && null === $avdl->getSupportStartDate())) {
            return max([
                $avdl->getDiagEndDate(),
                $avdl->getSupportEndDate(),
            ]);
        }

        return null;
    }

    /**
     * Donne le coefficient du suivi AVDL.
     */
    protected function getAvdlCoeffSupport(Avdl $avdl): float
    {
        // Si prêt au logement (PAL) : coeff. 0.25
        if (1 === $avdl->getSupportType()) {
            return SupportGroup::COEFFICIENT_QUARTER;
        }
        // Si accompagnement lourd : coeff. 2
        if (5 === $avdl->getSupportType()) {
            return SupportGroup::COEFFICIENT_DOUBLE;
        }
        // Sinon par défaut : coeff. 1
        return SupportGroup::COEFFICIENT_DEFAULT;
    }
}
