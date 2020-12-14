<?php

namespace App\Service\Export;

use App\Entity\Organization\Accommodation;
use App\Service\ExportExcel;

class AccommodationExport extends ExportExcel
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
            if ($i > 100) {
                sleep(5);
                $i = 1;
            }
            ++$i;
        }

        $this->createSheet('export_places', 'xlsx', $arrayData);

        return $this->exportFile();
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
            'Service' => $accommodation->getService() ? $accommodation->getService()->getName() : null,
            'Dispositif' => $accommodation->getDevice() ? $accommodation->getDevice()->getName() : null,
            'Nombre de places' => $accommodation->getNbPlaces(),
            "Date d'ouverture" => $this->formatDate($accommodation->getStartDate()),
            'Date de fermeture' => $this->formatDate($accommodation->getEndDate()),
            'Adresse' => $accommodation->getAddress(),
            'Ville' => $accommodation->getCity(),
            'Code postal' => $accommodation->getZipcode(),
            'Type' => $accommodation->getAccommodationType() ? $accommodation->getAccommodationTypeToString() : null,
            'Configuration (Diffus ou regroupé)' => $accommodation->getConfiguration() ? $accommodation->getConfigurationToString() : null,
            'Individuel ou collectif' => $accommodation->getIndividualCollective() ? $accommodation->getIndividualCollectiveToString() : null,
            'Commentaire' => $accommodation->getComment(),
            'Occupation actuelle (Nb de personnes)' => $numberPeople,
        ];
    }
}
