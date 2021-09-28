<?php

namespace App\Command\Support;

use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour l'adresse des suivis via l'API adresse.data.gouv.fr (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateSupportsGeoAPICommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:update_geo_api';

    protected $repo;
    protected $manager;

    public function __construct(SupportGroupRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateLocationSupports();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }

    /**
     * Met à jour les adresses des suivis.
     */
    protected function updateLocationSupports()
    {
        $count = 0;
        $supports = $this->repo->findAll();
        foreach ($supports as $support) {
            if (null === $support->getLocationId() && $count < 10) {
                $valueSearch = $support->getAddress().'+'.$support->getCity();
                $valueSearch = $this->cleanString($valueSearch);
                $geo = '&lat=49.04&lon=2.04';
                $url = 'https://api-adresse.data.gouv.fr/search/?q='.$valueSearch.$geo.'&limit=1';
                $raw = file_get_contents($url);
                $json = json_decode($raw);

                if (count($json->features)) {
                    $feature = $json->features[0];
                    if ($feature->properties->score > 0.4) {
                        $support
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

        return "[OK] The address of supports are update ! \n ".$count.' / '.count($supports);
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
