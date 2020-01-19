<?php

namespace App\Export;

use App\Entity\Service;
use App\Service\Export;

use App\Service\ObjectToArray;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ServiceExport
{
    protected $arrayData;

    /**
     * @var Service $service
     */
    protected $service;

    public function __construct()
    {
        $this->arrayData = [];
        $this->objectToArray = new ObjectToArray();
    }

    /**
     * Exporte les données
     */
    public function exportData($services)
    {
        $headers = $this->getHeaders();
        $this->arrayData[] = $headers;

        foreach ($services as $service) {
            $this->service = $service;

            $row = $this->getRow();

            $this->arrayData[] = $row;
        }

        $alphas = range("A", "Z");
        $columnsWithDate = [];
        foreach ($headers as $key => $value) {
            if (stristr($value, "Date"))
                $columnsWithDate[] = $alphas[$key];
        }

        $export = new Export("export_utilisateurs", "xlsx", $this->arrayData,  $columnsWithDate, null);

        return $export->exportFile();
    }

    /**
     *  Retourne les entêtes du tableau
     */
    protected function getHeaders()
    {
        $headers = [
            "N° service",
            "Service",
            "Pôle",
            "Téléphone",
            "Email",
            "Adresse",
            "Ville",
            // "Utilisateurs",
            "Date de création"
        ];

        return $headers;
    }

    /**
     * Retourne une ligne de résultats
     */
    protected function getRow()
    {
        // foreach ($this->service->getserviceUser() as $serviceService) {
        //     $users[] = $serviceService->getUser()->getFullname();
        // }

        return [
            $this->service->getId(),
            $this->service->getName(),
            $this->service->getPole()->getName(),
            $this->service->getPhone(),
            $this->service->getEmail(),
            $this->service->getAddress(),
            $this->service->getCity(),
            // join($users, ", "),
            Date::PHPToExcel($this->service->getCreatedAt()->format("d/m/Y")),
        ];
    }
}
