<?php

namespace App\DataFixtures;

use App\Entity\Organization\Device;
use App\Entity\Organization\Organization;
use App\Entity\Organization\Place;
use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceDevice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class AppFixtures extends Fixture
{
    private $manager;

    public const ORGANIZATION = [
        'ESPERER 95',
        'CCAS',
        'Conseil Départemental',
        'CHRS',
        'Autre',
    ];

    public const POLES = [
        1 => 'Hébergement',
        2 => 'Habitat',
    ];

    public const SERVICES_HABITAT = [
        'ALTHO',
        'ASSLT',
        'AVDL',
        'PASH',
    ];

    public const SERVICES_HEB = [
        'CHU les Carrières',
        'CHRS Etape',
        'DHUA',
        "CHRS L'Ensemble",
        "Maison Relais L'Ensemble",
        'Maison Milada',
    ];

    public const DEVICES = [
        1 => 'ALT',
        2 => 'ALTHO',
        3 => 'ASLL',
        4 => 'AVDL',
        5 => "Hébergement d'insertion",
        6 => "Hébergement d'urgence",
        7 => 'Maison relais',
    ];

    public $organization = null;
    public $services = [];
    public $devices = [];

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // Créé les dispositifs
        $this->createDevices();
        //Crée les pôles d'activité
        foreach (self::POLES as $key => $name) {
            $pole = $this->createPole($key, $name);

            if (1 === $key) {
                $this->addData($pole, $this::SERVICES_HEB);
            }
            // if (2 === $key) {
            //     $this->addData($pole, $this::SERVICES_HABITAT);
            // }
        }

        $this->manager->flush();
    }

    public function addData(Pole $pole, array $services)
    {
        //Créee les services d'activité
        foreach ($services as $service) {
            $service = $this->createService($pole, $service);
        }
    }

    public function createOrganizations()
    {
        foreach (self::ORGANIZATION as $value) {
            $organization = (new Organization())
                ->setName($value);

            $this->manager->persist($organization);

            if (null === $this->organization) {
                $this->organization = $organization;
            }
        }
    }

    public function createPole($key, string $name): Pole
    {
        $pole = (new Pole())
            ->setName($name)
            ->setOrganization($this->organization)
            ->setColor(3 === $key ? 'brown' : 'dark')
            ->setCreatedAt(new \DateTime());

        $this->manager->persist($pole);

        return $pole;
    }

    public function createService(Pole $pole, string $name): Service
    {
        $service = (new Service())
            ->setName($name)
            ->setPlace(true)
            ->setPole($pole);

        $this->manager->persist($service);

        $this->services[] = $service;
        $this->addServiceDevice($service);

        return $service;
    }

    protected function addServiceDevice(Service $service): void
    {
        $serviceDevice = (new ServiceDevice())
            ->setService($service);

        foreach ($this->devices as $device) {
            if ($device->getName() === $service->getName()) {
                $serviceDevice->setDevice($device);

                $this->manager->persist($serviceDevice);

                $this->createPlaces($service, $device); // Fixtures
            }
        }
    }

    protected function createPlaces(Service $service, Device $device): void
    {
        for ($i = 0; $i < mt_rand(5, 10); ++$i) {
            $place = (new Place())
                ->setService($service)
                ->setDevice($device)
                ->setName('Logement '.mt_rand(1, 100))
                ->setNbPlaces(mt_rand(2, 5))
                ->setStartDate($this->faker->dateTimeBetween('-5years', '-12months'))
                ->setCity('Cergy-Pontoise');

            $this->manager->persist($place);
        }
    }

    public function createDevices(): void
    {
        foreach (self::DEVICES as $key => $name) {
            $device = (new Device())
                ->setName($name);

            $this->manager->persist($device);

            $this->devices[] = $device;
        }
    }

    public static function getDateTimeBeetwen($startEnd, $endDate = 'now')
    {
        $faker = \Faker\Factory::create('fr_FR');

        return $faker->dateTimeBetween($startEnd, $endDate, $timezone = null);
    }

    public static function getStartDate($date)
    {
        $interval = (new \DateTime())->diff($date);
        $days = $interval->days;

        return '-'.$days.' days';
    }
}
