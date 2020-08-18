<?php

namespace App\Service\SupportGroup;

use App\Entity\Avdl;
use App\Entity\SupportGroup;

class AvdlService
{
    /**
     * Met à jour le suivi social d'un AVDL.
     */
    public function updateSupportGroup(SupportGroup $supportGroup): SupportGroup
    {
        $avdl = $supportGroup->getAvdl();

        $supportGroup
            ->setStatus($this->getAvdlStatus($avdl))
            ->setStartDate($this->getAvdlStartDate($avdl))
            ->setEndDate($this->getAvdlEndDate($avdl))
            ->setCoefficient($this->getAvdlCoeffSupport($avdl));

        $this->updateSupportPeople($supportGroup);

        return $supportGroup;
    }

    /**
     * Met à jour les suivis individuelles des personnes en AVDL.
     */
    protected function updateSupportPeople(SupportGroup $supportGroup): void
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if (null == $supportPerson->getEndStatus()) {
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
        if (null == $avdl->getDiagStartDate() && null == $avdl->getSupportStartDate()) {
            return SupportGroup::STATUS_PRE_ADD_IN_PROGRESS;
        }

        if (($avdl->getDiagEndDate() && $avdl->getSupportStartDate() == null) || $avdl->getSupportEndDate()) {
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
        if ($avdl->getSupportEndDate() || ($avdl->getDiagEndDate() && $avdl->getSupportStartDate() == null)) {
            return max([
                $avdl->getDiagEndDate(),
                $avdl->getSupportEndDate(),
            ]);
        }

        return null;
    }

    /**
     * Donne le coefficient du suivi AVDL.
     *
     * @return float
     */
    protected function getAvdlCoeffSupport(Avdl $avdl): float
    {
        // Si prêt au logement (PAL) : coeff. 0.25
        if ($avdl->getSupportType() == 1) {
            return SupportGroup::COEFFICIENT_QUARTER;
        }
        // Si accompagnement lourd : coeff. 2
        if ($avdl->getSupportType() == 5) {
            return SupportGroup::COEFFICIENT_DOUBLE;
        }
        // Sinon par défaut : coeff. 1
        return SupportGroup::COEFFICIENT_DEFAULT;
    }
}
