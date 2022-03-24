<?php

namespace App\Service\Export;

use App\Entity\Organization\User;
use App\Service\ExportExcel;
use Symfony\Component\HttpFoundation\Response;

class UserExport extends ExportExcel
{
    /**
     * Exporte les données.
     *
     * @param User[] $users
     */
    public function exportData(array $users): Response
    {
        $arrayData[] = array_keys($this->getDatas($users[0]));

        foreach ($users as $user) {
            $arrayData[] = $this->getDatas($user);
        }

        $this->createSheet($arrayData, [
            'name' => 'export_utilisateurs',
            'columnsWidth' => 15,
        ]);

        return $this->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(User $user): array
    {
        $services = [];
        $poles = [];
        foreach ($user->getServices() as $service) {
            $services[] = $service->getName();
            $pole = $service->getPole()->getName();
            if (!in_array($pole, $poles)) {
                $poles[] = $pole;
            }
        }

        return [
            'N° Utilisateur' => $user->getId(),
            'Nom' => $user->getLastname(),
            'Prénom' => $user->getFirstname(),
            'Statut' => $user->getStatusToString(),
            'Email' => $user->getEmail(),
            'Téléphone' => $user->getPhone1(),
            'Service' => join(', ', $services),
            'Pôle' => join(', ', $poles),
            'Date de création' => $this->formatDate($user->getCreatedAt()),
        ];
    }
}
