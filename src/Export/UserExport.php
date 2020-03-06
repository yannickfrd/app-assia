<?php

namespace App\Export;

use App\Entity\User;
use App\Service\Export;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UserExport
{
    /**
     * Exporte les données
     */
    public function exportData($users)
    {
        $arrayData[] = array_keys($this->getDatas($users[0]));

        foreach ($users as $user) {
            $arrayData[] = $this->getDatas($user);
        }

        $export = new Export("export_utilisateurs", "xlsx", $arrayData, null);

        return $export->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau
     * @param User $user
     * @return array
     */
    protected function getDatas(User $user)
    {
        $services = [];
        $poles = [];
        foreach ($user->getServiceUser() as $serviceUser) {
            $services[] = $serviceUser->getService()->getName();
            $pole = $serviceUser->getService()->getPole()->getName();
            if (!in_array($pole, $poles))
                $poles[] = $pole;
        }

        return [
            "N° Utilisateur" => $user->getId(),
            "Nom" => $user->getLastname(),
            "Prénom" => $user->getFirstname(),
            "Fonction" => $user->getStatusToString(),
            "Email" => $user->getEmail(),
            "Téléphone" => $user->getPhone(),
            "Service" => join($services, ", "),
            "Pôle" => join($poles, ", "),
            "Date de création" => Date::PHPToExcel($user->getCreatedAt()->format("d/m/Y")),
        ];
    }
}
