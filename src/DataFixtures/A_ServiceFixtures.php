<?php

namespace App\DataFixtures;

use App\Entity\Accommodation;
use App\Entity\Device;
use App\Entity\Pole;
use App\Entity\Service;
use App\Entity\ServiceDevice;
use App\Entity\ServiceUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class A_ServiceFixtures extends Fixture
{
    private $manager;

    public const SERVICES_HABITAT = [
        1 => 'ALTHO',
        2 => 'ASSLT - ASLLT',
        3 => '10 000 logements',
        4 => 'SAVL',
        5 => 'AVDL',
    ];

    public const SERVICES_HEB = [
        1 => 'CHU les Carrières',
        2 => 'CHRS Etape',
        3 => 'DHUA',
        4 => "Accueil de jour L'Ensemble",
        5 => "Accueil de nuit L'Ensemble",
        6 => "CHRS L'Ensemble",
        7 => "Maison Relais L'Ensemble",
        8 => "Taxi social L'Ensemble",
        9 => 'Maison Milada',
        10 => 'Maison Lucien',
        11 => 'MHU Oasis',
    ];

    public const SERVICES_SOCIO = [
        1 => 'CHRS Hermitage',
        2 => 'Consultations psychologiques',
        3 => 'DAVC',
        4 => 'DLSAP',
        5 => 'PE 78',
        6 => 'PE 95',
        7 => 'Pré-sentenciel',
    ];

    public const DEVICES = [
        1 => 'ALT',
        2 => 'ALTHO',
        3 => 'ASLLL',
        4 => 'AVDL',
        5 => "Hébergement d'insertion",
        6 => "Hébergement d'urgence",
        7 => 'Maison relais',
        8 => 'ASSLT - ASLLT',
        9 => '10 000 logements',
    ];

    private $pole;
    public $poles = [];
    private $service;
    public $services = [];
    public $devices = [];
    private $serviceUser;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->manager = $manager;
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    /**
     * @return Collection|Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    public function load(ObjectManager $manager)
    {
        // Créé les dispositifs
        $this->createDevices();
        //Crée les pôles d'activité
        foreach (Pole::POLES as $key => $pole) {
            $this->addPoles($key, $pole);
            switch ($key) {
                case 3:
                    $this->addData($this::SERVICES_HABITAT);
                    break;
                    // case 4:
                    //     $this->addData($this::SERVICES_HEB);
                    //     break;
            }
        }

        $this->manager->flush();
    }

    public function addData($services)
    {
        //Créee les services d'activité
        foreach ($services as $service) {
            $this->addService($service);
            // Crée des faux utilisateurs
            for ($i = 1; $i <= 5; ++$i) {
                $this->addServiceUser();
            }
        }
    }

    // Crée les pôles
    public function addPoles($key, $service)
    {
        $this->pole = new Pole();

        $color = 'blue';

        switch ($key) {
            case 3:
                $color = 'brown';
                break;
            case 4:
                $color = 'orange2';
                break;
        }

        $this->pole->setName($service)
            ->setColor($color)
            ->setCreatedAt(new \DateTime());

        $this->manager->persist($this->pole);
    }

    // Crée les services
    public function addService($service)
    {
        $this->service = new Service();

        $this->service->setName($service)
            ->setAccommodation(true)
            ->setPole($this->pole)
            ->setCreatedAt(new \DateTime());

        $this->services[] = $this->service;

        $this->manager->persist($this->service);

        $this->addServiceDevice();
    }

    protected function addServiceDevice()
    {
        $serviceDevice = new ServiceDevice();

        $serviceDevice->setService($this->service);

        foreach ($this->devices as $device) {
            if ($device->getName() == $this->service->getName()) {
                $serviceDevice->setDevice($device);

                $this->manager->persist($serviceDevice);

                $this->addAccommodations($device); // Fixtures
            }
        }
    }

    protected function addAccommodations($device)
    {
        for ($i = 0; $i < mt_rand(5, 10); ++$i) {
            $place = new Accommodation();

            $place->setService($this->service)
                ->setDevice($device)
                ->setName('Logement '.mt_rand(1, 100))
                ->setPlacesNumber(mt_rand(2, 5))
                ->setOpeningDate($this->faker->dateTimeBetween('-5years', '-12months'))
                ->setCity('Cergy-Pontoise');

            $this->manager->persist($place);
        }
    }

    // Crée la liaison entre le service et l'utilisateur
    public function addServiceUser()
    {
        $this->serviceUser = new ServiceUser();

        $this->serviceUser->setRole(1)
            ->setService($this->service);

        $this->manager->persist($this->serviceUser);
    }

    public function createDevices()
    {
        foreach (self::DEVICES as $key => $value) {
            $device = new Device();

            $device->setName($value)
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime());

            $this->devices[] = $device;

            $this->manager->persist($device);
        }
    }
}
