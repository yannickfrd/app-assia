<?php

namespace App\Command;

use App\Repository\AccommodationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour l'adresse des groupes de places via l'API adresse.data.gouv.fr (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateAccommodationsGeoAPICommand extends Command
{
    protected static $defaultName = 'app:accommodation:update:geo_api';

    protected $repo;
    protected $manager;

    public function __construct(AccommodationRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateLocationAccommodations();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Met à jour les adresses des groupes de places.
     */
    protected function updateLocationAccommodations()
    {
        $listenersType = $this->manager->getEventManager()->getListeners();
        foreach ($listenersType as $listenerType) {
            foreach ($listenerType as $listener) {
                $this->manager->getEventManager()->removeEventListener(['onFlush', 'onFlush'], $listener);
            }
        }

        $count = 0;
        $accommodations = $this->repo->findAll();
        foreach ($accommodations as $accommodation) {
            if (null == $accommodation->getLocationId() && $count < 10) {
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
                    $this->manager->flush();
                    ++$count;
                }
            }
        }

        return "[OK] The address of accommodations are update ! \n ".$count.' / '.count($accommodations);
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
}
