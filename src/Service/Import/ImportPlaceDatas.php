<?php

namespace App\Service\Import;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Organization\SubServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class ImportPlaceDatas extends ImportDatas
{
    protected $em;

    protected $items = [];
    protected $placeRepo;
    protected $subServiceRepo;
    protected $field;
    protected $subServices = [];

    public function __construct(EntityManagerInterface $em, PlaceRepository $placeRepo, SubServiceRepository $subServiceRepo)
    {
        $this->em = $em;
        $this->placeRepo = $placeRepo;
        $this->subServiceRepo = $subServiceRepo;
    }

    /**
     * Importe les données.
     *
     * @param Collection<Service> $services
     */
    public function importInDatabase(string $fileName, ArrayCollection $services): array
    {
        $this->fields = $this->getDatas($fileName);

        $i = 0;
        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $this->items[] = $this->createPlace($services);
            }
            ++$i;
        }

        $this->em->flush();

        return $this->items;
    }

    /**
     * @param Collection<Service> $services
     */
    protected function createPlace(ArrayCollection $services): ?Place
    {
        $placeExists = $this->placeRepo->findOneBy([
            'name' => $this->field['Nom'],
        ]);

        if ('Non' == $this->field['Nom'] || $placeExists) {
            return null;
        }

        foreach ($services as $service) {
            $place = (new Place())
                ->setService($service)
                ->setSubService($this->getSubService())
                ->setName($this->field['Nom'])
                ->setPlaceType(3)
                ->setConfiguration(2)
                ->setIndividualCollective(1)
                ->setAddress($this->field['Adresse'])
                ->setCity($this->field['Commune']);

            $this->updateLocation($place);

            $this->em->persist($place);
        }

        return $place;
    }

    protected function cleanString(string $string): string
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

    protected function updateLocation(Place $place): void
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

    protected function getSubService(): ?SubService
    {
        $subServiceName = $this->field['Secteur'];

        if (key_exists($subServiceName, $this->subServices)) {
            return $this->subServices[$subServiceName];
        }

        $subService = $this->subServiceRepo->findOneBy([
            'name' => $subServiceName,
        ]);

        if ($subService) {
            $this->subServices[$subServiceName] = $subService;

            return $subService;
        }

        return null;
    }
}
