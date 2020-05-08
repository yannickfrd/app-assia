<?php

namespace App\Export;

use App\Entity\User;
use App\Service\ExportService;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UserExport
{
    /**
     * Exporte les données.
     */
    public function exportData($users)
    {
        $arrayData[] = array_keys($this->getDatas($users[0]));

        foreach ($users as $user) {
            $arrayData[] = $this->getDatas($user);
        }

        return (new ExportService('export_utilisateurs', 'xlsx', $arrayData, null))->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(User $user): array
    {
        $services = [];
        $poles = [];
        foreach ($user->getServiceUser() as $serviceUser) {
            $services[] = $serviceUser->getService()->getName();
            $pole = $serviceUser->getService()->getPole()->getName();
            if (!in_array($pole, $poles)) {
                $poles[] = $pole;
            }
        }

        return [
            'N° Utilisateur' => $user->getId(),
            'Nom' => $user->getLastname(),
            'Prénom' => $user->getFirstname(),
            'Fonction' => $user->getStatusToString(),
            'Email' => $user->getEmail(),
            'Téléphone' => $user->getPhone1(),
            'Service' => join(', ', $services),
            'Pôle' => join(', ', $poles),
            'Date de création' => Date::PHPToExcel($user->getCreatedAt()->format('d/m/Y')),
        ];
    }
}
