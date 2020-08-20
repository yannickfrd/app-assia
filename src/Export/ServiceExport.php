<?php

namespace App\Export;

use App\Entity\Service;
use App\Service\ExportExcel;

class ServiceExport
{
    use ExportExcelTrait;

    /**
     * Exporte les données.
     */
    public function exportData($services)
    {
        $arrayData[] = array_keys($this->getDatas($services[0]));

        foreach ($services as $service) {
            $arrayData[] = $this->getDatas($service);
        }

        return (new ExportExcel('export_services', 'xlsx', $arrayData, null))->exportFile();
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
