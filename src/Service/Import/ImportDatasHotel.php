<?php

namespace App\Service\Import;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Organization\SubServiceRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportDatasHotel extends ImportDatas
{
    protected $manager;

    protected $fields;
    protected $field;

    protected $items = [];
    protected $repoPlace;
    protected $repoSubService;
    protected $localities;

    public function __construct(
        EntityManagerInterface $manager,
        PlaceRepository $repoPlace,
        SubServiceRepository $repoSubService)
    {
        $this->manager = $manager;
        $this->repoPlace = $repoPlace;
        $this->repoSubService = $repoSubService;
        $this->localities = $this->getLocalities();
    }

    public function importInDatabase(string $fileName, Service $service): int
    {
        $this->fields = $this->getDatas($fileName);

        $i = 0;
        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $this->items[] = $this->createPlace($service);
            }
            ++$i;
        }

        $this->manager->flush();

        return count($this->items);
    }

    protected function createPlace(Service $service): ?Place
    {
        $placeExists = $this->repoPlace->findOneBy([
            'name' => $this->field['Nom'],
        ]);

        if ('Non' === $this->field['Nom'] || $placeExists) {
            return null;
        }

        $place = (new Place())
            ->setService($service)
            ->setSubService($this->field['Secteur'] ? $this->localities[$this->field['Secteur']] : null)
            ->setName(str_replace('HOTEL - ', '', $this->field['Nom']))
            ->setPlaceType(3)
            ->setConfiguration(2)
            ->setIndividualCollective(1)
            ->setAddress($this->field['Adresse'])
            ->setCity($this->field['Commune'])
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        $this->updateLocation($place);

        $this->manager->persist($place);

        return $place;
    }

    protected function cleanString(string $string)
    {
        $string = strtr($string, [
            'à' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
        ]);
        $string = strtolower($string);
        $string = str_replace(' ', '+', $string);

        return $string;
    }

    protected function updateLocation(Place $place)
    {
        $valueSearch = $place->getAddress().'+'.$place->getCity();
        $valueSearch = $this->cleanString($valueSearch);
        $geo = '&lat=49.04&lon=2.04';
        $url = 'https://api-adresse.data.gouv.fr/search/?q='.$valueSearch.$geo.'&limit=1';
        $raw = file_get_contents($url);
        $json = json_decode($raw);

        if (count($json->features)) {
            $feature = $json->features[0];
            if ($feature->properties->score > 0.4) {
                $place
                    ->setCity($feature->properties->city)
                    ->setAddress($feature->properties->name)
                    ->setZipcode($feature->properties->postcode)
                    ->setLocationId($feature->properties->id)
                    ->setLon($feature->geometry->coordinates[0])
                    ->setLat($feature->geometry->coordinates[1]);
            }
        }
    }

    protected function getLocalities()
    {
        return [
            'Cergy-Pontoise' => $this->repoSubService->find(1),
            'Pays de France' => $this->repoSubService->find(1),
            'Plaine de France' => $this->repoSubService->find(2),
            'Rives de Seine' => $this->repoSubService->find(3),
            'Vallée de Montmorency' => $this->repoSubService->find(3),
        ];
    }
}
