<?php

namespace App\Service\Import;

use App\Entity\Accommodation;
use App\Entity\Service;
use App\Repository\AccommodationRepository;
use App\Repository\SubServiceRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportDatasHotel
{
    use ImportTrait;

    protected $manager;

    protected $items = [];
    protected $repoAccommodation;
    protected $repoSubService;
    protected $field;
    protected $localities;

    public function __construct(EntityManagerInterface $manager, AccommodationRepository $repoAccommodation, SubServiceRepository $repoSubService)
    {
        $this->manager = $manager;
        $this->repoAccommodation = $repoAccommodation;
        $this->repoSubService = $repoSubService;
        $this->localities = $this->getLocalities();
    }

    public function importInDatabase(string $fileName, Service $service): array
    {
        $this->fields = $this->getDatas($fileName);

        $i = 0;
        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $this->items[] = $this->createAccommodation($service);
            }
            ++$i;
        }

        dd($this->items);
        $this->manager->flush();

        return $this->items;
    }

    protected function createAccommodation(Service $service): ?Accommodation
    {
        $accommodationExists = $this->repoAccommodation->findOneBy([
            'name' => $this->field['Nom'],
        ]);

        if ($this->field['Nom'] == 'Non' || $accommodationExists) {
            return null;
        }

        $accommodation = (new Accommodation())
            ->setService($service)
            ->setSubService($this->field['Secteur'] ? $this->localities[$this->field['Secteur']] : null)
            ->setName(str_replace('HOTEL - ', '', $this->field['Nom']))
            ->setAccommodationType(3)
            ->setConfiguration(2)
            ->setIndividualCollective(1)
            ->setAddress($this->field['Adresse'])
            ->setCity($this->field['Commune'])
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        // $this->updateLocation($accommodation);

        $this->manager->persist($accommodation);

        return $accommodation;
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

    protected function updateLocation(Accommodation $accommodation)
    {
        $valueSearch = $accommodation->getAddress().'+'.$accommodation->getCity();
        $valueSearch = $this->cleanString($valueSearch);
        $geo = '&lat=49.04&lon=2.04';
        $url = 'https://api-adresse.data.gouv.fr/search/?q='.$valueSearch.$geo.'&limit=1';
        $raw = file_get_contents($url);
        $json = json_decode($raw);

        if (count($json->features)) {
            $feature = $json->features[0];
            if ($feature->properties->score > 0.4) {
                $accommodation
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
