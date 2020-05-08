<?php

namespace App\Export;

use App\Entity\Service;
use App\Service\ExportService;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ServiceExport
{
    /**
     * Exporte les données.
     */
    public function exportData($services)
    {
        $arrayData[] = array_keys($this->getDatas($services[0]));

        foreach ($services as $service) {
            $arrayData[] = $this->getDatas($service);
        }

        return (new ExportService('export_services', 'xlsx', $arrayData, null))->exportFile();
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
            'Date de création' => Date::PHPToExcel($service->getCreatedAt()->format('d/m/Y')),
        ];
    }
}
