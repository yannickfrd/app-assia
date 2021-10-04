<?php

namespace App\Service\Export;

use App\Entity\Organization\Place;
use App\Service\ExportExcel;

class PlaceExport extends ExportExcel
{
    protected $arrayData;

    public function __construct()
    {
        $this->arrayData = [];
    }

    /**
     * Exporte les données.
     */
    public function exportData($places)
    {
        $arrayData = [];
        $i = 0;

        foreach ($places as $place) {
            if (0 === $i) {
                $arrayData[] = array_keys($this->getDatas($place));
            }
            $arrayData[] = $this->getDatas($place);
            ++$i;
        }

        $this->createSheet($arrayData, [
            'name' => 'export_places',
        ]);

        return $this->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    public function getDatas(Place $place): array
    {
        $numberPeople = 0;
        foreach ($place->getPlaceGroups() as $placeGroup) {
            foreach ($placeGroup->getPlacePeople() as $placePerson) {
                $endDate = $placePerson->getEndDate();
                if (!$endDate || $endDate->format('d/m/Y') >= (new \Datetime())->format('d/m/Y')) {
                    ++$numberPeople;
                }
            }
        }
        $service = $place->getService();

        return [
            'Nom du groupe de places' => $place->getName(),
            'Pôle' => $service ? $service->getPole()->getName() : null,
            'Service' => $service ? $service->getName() : null,
            'Dispositif' => $place->getDevice() ? $place->getDevice()->getName() : null,
            'Nombre de places' => $place->getNbPlaces(),
            "Date d'ouverture" => $this->formatDate($place->getStartDate()),
            'Date de fermeture' => $this->formatDate($place->getEndDate()),
            'Adresse' => $place->getAddress(),
            'Ville' => $place->getCity(),
            'Code postal' => $place->getZipcode(),
            'Type' => $place->getPlaceType() ? $place->getPlaceTypeToString() : null,
            'Configuration (Diffus ou regroupé)' => $place->getConfiguration() ? $place->getConfigurationToString() : null,
            'Individuel ou partagé' => $place->getIndividualCollective() ? $place->getIndividualCollectiveToString() : null,
            'Commentaire' => $place->getComment(),
            'Occupation actuelle (Nb de personnes)' => $numberPeople,
        ];
    }
}
