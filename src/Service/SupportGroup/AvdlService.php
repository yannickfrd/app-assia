<?php

namespace App\Service\SupportGroup;

use App\Entity\Avdl;
use App\Entity\SupportGroup;

class AvdlService
{
    /**
     * Met à jour l'AVDL.
     */
    public function updateAvdl(SupportGroup $supportGroup)
    {
        $avdl = $supportGroup->getAvdl();

        return $supportGroup
            ->setStatus($this->getAvdlStatus($avdl))
            ->setStartDate($this->getAvdlStartDate($avdl))
            ->setEndDate($this->getAvdlEndDate($avdl))
            ->setCoefficient($this->getAvdlCoeffSupport($avdl));
    }

    /**
     * Donne le statut du suivi AVDL.
     */
    protected function getAvdlStatus(Avdl $avdl): int
    {
        if ($avdl->getSupportEndDate() || ($avdl->getDiagEndDate() && $avdl->getSupportStartDate() == null)) {
            return 4; // Terminé
        }

        return 2; // En cours
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
    protected function getAvdlCoeffSupport(Avdl $avdl): int
    {
        // Si accompagnement lourd : coeff 2
        if ($avdl->getSupportType() == 3) {
            return 2;
        }
        // Si prêt au logement : coeff 0.25
        if ($avdl->getReadyToHousing() == 1) {
            return 0.25;
        }
        // Sinon par défaut : coeff 1
        return 1;
    }
}
