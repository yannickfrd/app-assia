<?php

namespace App\Command\Evaluation;

use App\Entity\Evaluation\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportAllCountriesCommand extends Command
{
    public const URL = 'https://www.citysearch-api.com/fr';

    protected static $defaultName = 'app:country:import-all';
    protected static $defaultDescription = 'Import all countries in database by API';

    protected $client;
    protected $em;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em)
    {
        $this->client = $client;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $response = $this->client->request('GET', self::URL.'/pays?login=APP_LOGIN&apikey=APP_KEY');

        $results = (json_decode($response->getContent()))->results;

        if (!(is_array($results))) {
            $io->error('Error !');

            return Command::FAILURE;
        }

        foreach ($results as $result) {
            $country = (new Country())
                ->setName($result->pays)
                ->setCode($result->code);

            $this->em->persist($country);
        }

        $this->em->flush();

        $io->success('Countries are imported!');

        return Command::SUCCESS;
    }
}
