<?php

namespace App\Command\Place;

use App\Repository\Organization\PlaceRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour l'adresse des groupes de places via l'API adresse.data.gouv.fr (TEMPORAIRE, A SUPPRIMER).
 */
class UpdatePlacesGeoAPICommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:place:update_geo_api';

    protected $repo;
    protected $manager;

    public function __construct(PlaceRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateLocationPlaces();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }

    /**
     * Met à jour les adresses des groupes de places.
     */
    protected function updateLocationPlaces()
    {
        $count = 0;
        $places = $this->repo->findAll();
        foreach ($places as $place) {
            if (null === $place->getLocationId() && $count < 10) {
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
                    $this->manager->flush();
                    ++$count;
                }
            }
        }

        return "[OK] The address of places are update ! \n ".$count.' / '.count($places);
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
