<?php

namespace App\Service\Export;

use App\Entity\Organization\Service;
use App\Service\ExportExcel;

class ServiceExport extends ExportExcel
{
    /**
     * Exporte les données.
     */
    public function exportData(array $services)
    {
        $arrayData[] = array_keys($this->getDatas($services[0]));

        foreach ($services as $service) {
            $arrayData[] = $this->getDatas($service);
        }

        $this->createSheet($arrayData, [
            'name' => 'export_services',
        ]);

        return $this->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(Service $service): array
    {
        return [
            'N° service' => $service->getId(),
            'Service' => $service->getName(),
            'Pôle' => $service->getPole()->getName(),
            'Téléphone' => $service->getPhone1(),
            'Email' => $service->getEmail(),
            'Adresse' => $service->getAddress(),
            'Ville' => $service->getCity(),
            'Date de création' => $this->formatDate($service->getCreatedAt()),
        ];
    }
}
