<?php

namespace App\Export;

use App\Entity\User;
use App\Service\Export;

use App\Service\ObjectToArray;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UserExport
{
    protected $arrayData;

    /**
     * @var User $user
     */
    protected $user;

    public function __construct()
    {
        $this->arrayData = [];
        $this->objectToArray = new ObjectToArray();
    }

    /**
     * Exporte les données
     */
    public function exportData($users)
    {
        $headers = $this->getHeaders();
        $this->arrayData[] = $headers;

        foreach ($users as $user) {
            $this->user = $user;

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
            "N° Utilisateur",
            "Nom",
            "Prénom",
            "Fonction",
            "Email",
            "Téléphone",
            "Service",
            "Pôle",
            "Date de création"
        ];

        return $headers;
    }

    /**
     * Retourne une ligne de résultats
     */
    protected function getRow()
    {
        $services = [];
        $poles = [];
        foreach ($this->user->getServiceUser() as $serviceUser) {
            $services[] = $serviceUser->getService()->getName();
            $poles[] = $serviceUser->getService()->getPole()->getName();
        }

        return [
            $this->user->getId(),
            $this->user->getLastname(),
            $this->user->getFirstname(),
            $this->user->getStatusList(),
            $this->user->getEmail(),
            $this->user->getPhone(),
            join($services, ", "),
            $poles[0],
            Date::PHPToExcel($this->user->getCreatedAt()->format("d/m/Y")),
        ];
    }
}
