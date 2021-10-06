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

    public const DEVICES = [
        1 => 'ALT',
        2 => 'ALTHO',
        3 => 'ASLLT',
        4 => 'AVDL hors-DALO',
        5 => 'Insertion - Regroupé',
        6 => 'HU - Diffus',
        7 => 'Maison relais',
        8 => 'ASLL',
        9 => '10 000 LA BA',
        10 => 'AVDL DALO',
        11 => 'AVDL (SAVL)',
        12 => 'XXX1',
        13 => '10 000 LA BD',
        14 => 'Famille AMH',
        15 => "ASE mise à l'abri",
        16 => 'ASE hébergement',
        17 => 'Injonctions Réfugiés',
        18 => 'Opération ciblée',
        19 => 'Accompagnement hôtel',
        20 => "Intervention d'urgence",
        21 => 'HU - Regroupé',
        26 => 'HU hiver',
    ];

    public const SERVICES = [
        0 => [
            'name' => 'CHU Pontoise',
            'pole' => 1,
            'devices' => [21],
        ],
        3 => [
            'name' => 'CHRS Cergy',
            'pole' => 1,
            'devices' => [5, 21],
        ],
        4 => [
            'name' => 'Maison Relais Cergy',
            'pole' => 1,
            'devices' => [7],
        ],
        10 => [
            'name' => 'ALTHO',
            'pole' => 2,
            'devices' => [2],
        ],
        11 => [
            'name' => 'ASSL',
            'pole' => 2,
            'devices' => [3],
        ],
        12 => [
            'name' => 'AVDL',
            'pole' => 2,
            'devices' => [4, 11],
        ],
        13 => [
            'name' => 'PASH',
            'pole' => 2,
            'devices' => [19, 20],
        ],
    ];

    public $organization = null;
    public $poles = [];
    public $devices = [];
    public $services = [];

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
        }

        //Crée les différents services
        foreach (self::SERVICES as $serviceArray) {
            $this->createService($serviceArray);
        }

        $this->manager->flush();
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
            ->setColor('dark')
            ->setCreatedAt(new \DateTime());

        $this->manager->persist($pole);

        $this->poles[$key] = $pole;

        return $pole;
    }

    public function createService(array $arrayService): Service
    {
        $service = (new Service())
            ->setName($arrayService['name'])
            ->setPlace(true)
            ->setPole($this->poles[$arrayService['pole']]);

        $this->manager->persist($service);

        $this->services[] = $service;

        $this->createServiceDevices($service, $arrayService['devices']);

        return $service;
    }

    protected function createServiceDevices(Service $service, array $arrayDevices): void
    {
        foreach ($arrayDevices as $deviceKey) {
            $device = $this->devices[$deviceKey];

            $serviceDevice = (new ServiceDevice())
                ->setService($service)
                ->setDevice($device);

            $this->manager->persist($serviceDevice);

            $this->createPlaces($service, $device);
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

            $this->devices[$key] = $device;
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
