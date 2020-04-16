<?php

namespace App\Export;

use App\Entity\Accommodation;
use App\Service\Export;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AccommodationExport
{
    protected $arrayData;

    public function __construct()
    {
        $this->arrayData = [];
    }

    /**
     * Exporte les données.
     */
    public function exportData($accommodations)
    {
        $arrayData = [];
        $i = 0;

        foreach ($accommodations as $accommodation) {
            if (0 == $i) {
                $arrayData[] = array_keys($this->getDatas($accommodation));
            }
            $arrayData[] = $this->getDatas($accommodation);
            ++$i;
        }

        return (new Export('export_suivis', 'xlsx', $arrayData, null))->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    public function getDatas(Accommodation $accommodation): array
    {
        $numberPeople = 0;
        foreach ($accommodation->getAccommodationGroups() as $accommodationGroup) {
            foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
                $endDate = $accommodationPerson->getEndDate();
                if (!$endDate || $endDate->format('d/m/Y') >= (new \Datetime())->format('d/m/Y')) {
                    ++$numberPeople;
                }
            }
        }

        return [
            'Nom du groupe de places' => $accommodation->getName(),
            'Service' => $accommodation->getService()->getName(),
            'Dispositif' => $accommodation->getDevice()->getName(),
            'Nombre de places' => $accommodation->getPlacesNumber(),
            "Date d'ouverture" => $this->formatDate($accommodation->getOpeningDate()),
            'Date de fermeture' => $this->formatDate($accommodation->getClosingDate()),
            'Adresse' => $accommodation->getAddress(),
            'Ville' => $accommodation->getCity(),
            'Code postal' => $accommodation->getZipCode(),
            'Type' => $accommodation->getAccommodationType() ? $accommodation->getAccommodationTypeToString() : null,
            'Configuration (Diffus ou regroupé)' => $accommodation->getConfiguration() ? $accommodation->getConfigurationToString() : null,
            'Individuel ou collectif' => $accommodation->getIndividualCollective() ? $accommodation->getIndividualCollectiveToString() : null,
            'Commentaire' => $accommodation->getComment(),
            'Occupation actuelle (Nb de personnes)' => $numberPeople,
        ];
    }

    public function formatDate($date)
    {
        return $date ? Date::PHPToExcel($date->format('Y-m-d')) : null;
    }
}
