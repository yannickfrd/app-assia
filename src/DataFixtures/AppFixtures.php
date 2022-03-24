<?php

namespace App\DataFixtures;

use App\Entity\Admin\Setting;
use App\Entity\Organization\Device;
use App\Entity\Organization\Organization;
use App\Entity\Organization\Place;
use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceDevice;
use App\Form\Utils\Choices;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class AppFixtures extends Fixture implements FixtureGroupInterface
{
    public const ORGANIZATION = [
        'ESPERER 95',
        'CCAS',
        'Conseil Départemental',
        'CHRS',
    ];

    /** @var ObjectManager */
    private $objectManager;

    /** @var \Faker\Generator */
    private $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;

        $this->createSetting();
        $this->createOrganizations();
        $this->createPoles();
        $this->createDevices();
        $this->createServices();

        $this->objectManager->flush();
    }

    private function createSetting()
    {
        $setting = (new Setting())->setOrganizationName('ESPERER 95');

        $this->objectManager->persist($setting);
    }

    private function createOrganizations(): void
    {
        $i = 0;
        foreach (self::ORGANIZATION as $value) {
            $organization = (new Organization())->setName($value);

            $this->objectManager->persist($organization);

            if (0 === $i) {
                $this->addReference('organization_0', $organization);
            }
            ++$i;
        }
    }

    private function createPoles(): void
    {
        foreach ($this->getDataPoles() as $key => [$name, $color]) {
            $pole = (new Pole())
                ->setName($name)
                ->setOrganization($this->getReference('organization_0'))
                ->setColor($color);

            $this->objectManager->persist($pole);

            $this->addReference('pole_'.$key, $pole);
        }
    }

    private function createServices(): void
    {
        foreach ($this->getDataServices() as [$serviceName, $poleId, $deviceIds, $type, $place, $contribution]) {
            $service = (new Service())
                ->setName($serviceName)
                ->setType($type)
                ->setPlace($place)
                ->setContribution($contribution)
                ->setPole($this->getReference('pole_'.$poleId));

            $this->objectManager->persist($service);

            $this->createServiceDevices($service, $deviceIds);
        }
    }

    private function createServiceDevices(Service $service, array $arrayDevices): void
    {
        foreach ($arrayDevices as $deviceKey) {
            $device = $this->getReference('device_'.$deviceKey);

            $serviceDevice = (new ServiceDevice())
                ->setService($service)
                ->setDevice($device);

            $this->objectManager->persist($serviceDevice);

            $this->createPlaces($service, $device);
        }
    }

    private function createPlaces(Service $service, Device $device): void
    {
        for ($i = 0; $i < mt_rand(5, 10); ++$i) {
            $place = (new Place())
                ->setService($service)
                ->setDevice($device)
                ->setName('Logement '.mt_rand(1, 100))
                ->setNbPlaces(mt_rand(2, 5))
                ->setStartDate($this->faker->dateTimeBetween('-5years', '-12months'))
                ->setCity('Cergy-Pontoise');

            $this->objectManager->persist($place);
        }
    }

    private function createDevices(): void
    {
        foreach ($this->getDataDevices() as $key => [$name, $place, $contribution]) {
            $device = (new Device())
                ->setCode($key)
                ->setPlace($place)
                ->setContribution($contribution)
                ->setName($name);

            $this->objectManager->persist($device);

            $this->addReference('device_'.$key, $device);
        }
    }

    private function getDataPoles(): array
    {
        return [
            1 => ['Hébergement', 'brown'],
            2 => ['Habitat', 'brown'],
        ];
    }

    private function getDataServices(): array
    {
        return [
            // 0 => [$serviceName, $poleId, $deviceIds, $type, $place, $contribution]
                1 => ['CHU Pontoise', 1, [22], 1, Choices::YES, Choices::YES],
                3 => ['CHRS Cergy ', 1, [5, 22], 1, Choices::YES, Choices::YES],
                4 => ['Maison Relais Cergy ', 1, [7], 1, Choices::YES, Choices::YES],
                10 => ['ALTHO ', 2, [2], 1, Choices::YES, Choices::YES],
                11 => ['ASSL ', 2, [3, 8], 1, Choices::NO, Choices::NO],
                12 => ['AVDL ', 2, [4, 10], 2, Choices::NO, Choices::NO],
                13 => ['PASH ', 2, [19, 20], 3, Choices::YES, Choices::YES],
        ];
    }

    private function getDataDevices(): array
    {
        return [
            // 0 => [$deviceName, $place, $contribution]
            2 => ['ALTHO', Choices::YES, Choices::YES],
            3 => ['ASSLT', Choices::NO, Choices::NO],
            4 => ['AVDL hors-DALO', Choices::NO, Choices::NO],
            5 => ['Insertion - Regroupé', Choices::YES, Choices::YES],
            7 => ['Maison relais', Choices::YES, Choices::YES],
            8 => ['ASSL', Choices::NO, Choices::NO],
            10 => ['AVDL DALO', Choices::NO, Choices::NO],
            19 => ['Accompagnement hôtel', Choices::NO, Choices::YES],
            20 => ["Intervention d'urgence", Choices::NO, Choices::NO],
            22 => ['HU - Regroupé', Choices::YES, Choices::YES],
        ];
    }

    public static function getDateTimeBeetwen($startEnd, $endDate = 'now'): \DateTime
    {
        $faker = \Faker\Factory::create('fr_FR');

        return $faker->dateTimeBetween($startEnd, $endDate, $timezone = null);
    }

    public static function getStartDate($date): string
    {
        $interval = (new \DateTime())->diff($date);
        $days = $interval->days;

        return '-'.$days.' days';
    }

    public static function getGroups(): array
    {
        return ['user', 'people', 'support', 'evaluation', 'note', 'rdv', 'task', 'document', 'payment', 'tag'];
    }
}
