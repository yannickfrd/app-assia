<?php

namespace App\Command\Place;

use App\Repository\Organization\PlaceRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Commande pour mettre à jour l'adresse des groupes de places via l'API adresse.data.gouv.fr (TEMPORAIRE, A SUPPRIMER).
 */
class UpdatePlacesGeoAPICommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:place:update_geo_api';
    protected static $defaultDescription = 'Update location in places with API adresse.data.gouv.fr';

    protected $placeRepo;
    protected $client;
    protected $em;

    public function __construct(PlaceRepository $placeRepo, HttpClientInterface $client, EntityManagerInterface $em)
    {
        $this->placeRepo = $placeRepo;
        $this->client = $client;
        $this->em = $em;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');

        $places = $this->placeRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $nbPlaces = count($places);
        $count = 0;

        $io->createProgressBar();
        $io->progressStart($nbPlaces);

        foreach ($places as $place) {
            if (null === $place->getLocationId() && $place->getAddress()) {
                $valueSearch = $place->getAddress().'+'.$place->getCity();
                $valueSearch = $this->cleanString($valueSearch);
                $geo = '&lat=49.04&lon=2.04';
                $url = 'https://api-adresse.data.gouv.fr/search/?q='.$valueSearch.$geo.'&limit=1';

                $response = $this->client->request('GET', $url);
                $content = json_decode($response->getContent());

                if (count($content->features)) {
                    $feature = $content->features[0];
                    if ($feature->properties->score > 0.4) {
                        $place
                            ->setCity($feature->properties->city)
                            ->setAddress($feature->properties->name)
                            ->setZipcode($feature->properties->postcode)
                            ->setLocationId($feature->properties->id)
                            ->setLon($feature->geometry->coordinates[0])
                            ->setLat($feature->geometry->coordinates[1]);
                    }
                    ++$count;
                }
            }

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();

        $io->success("The address of places are update ! \n ".$count.' / '.$nbPlaces);

        return Command::SUCCESS;
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
}
