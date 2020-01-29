<?php

namespace App\DataFixtures;

use App\Entity\Pole;
use App\Entity\User;
use App\Entity\Device;
use App\Entity\Service;
use App\Entity\ServiceUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\Collection;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class A_ServiceFixtures extends Fixture
{
    private $manager;

    public const SERVICES_HABITAT = [
        1 => "ALTHO",
        2 => "ASSLT - ASLLT",
        3 => "10 000 logements",
        4 => "SAVL",
        5 => "AVDL"
    ];

    public const SERVICES_HEB = [
        1 => "CHU les Carrières",
        2 => "CHRS Etape",
        3 => "DHUA",
        4 => "Accueil de jour L'Ensemble",
        5 => "Accueil de nuit L'Ensemble",
        6 => "CHRS L'Ensemble",
        7 => "Maison Relais L'Ensemble",
        8 => "Taxi social L'Ensemble",
        9 => "Maison Milada",
        10 => "Maison Lucien",
        11 => "MHU Oasis"
    ];

    public const SERVICES_SOCIO = [
        1 => "CHRS Hermitage",
        2 => "Consultations psychologiques",
        3 => "DAVC",
        4 => "DLSAP",
        5 => "PE 78",
        6 => "PE 95",
        7 => "Pré-sentenciel"
    ];

    public const DEVICES = [
        1 => "Hébergement d'urgence",
        2 => "Hébergement de stabilisation",
        3 => "Hébergement d'insertion",
        4 => "ALT",
        5 => "ALTHO",
        6 => "Maison relais",
        7 => "Résidence sociale",
        8 => "Hébergement d'urgence hivernale",
        9 => "AVDL",
        10 => "ASLLL",
    ];

    private $pole;
    public $poles = [];
    private $service;
    public $services = [];
    private $serviceUser;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->manager = $manager;
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create("fr_FR");
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
        $this->createDevices();

        $this->manager->flush();
    }

    public function addData($services)
    {
        //Créee les services d'activité
        foreach ($services as $service) {
            $this->addService($service);
            // Crée des faux utilisateurs
            for ($i = 1; $i <= mt_rand(2, 5); $i++) {
                $this->addServiceUser();
            }
        }
    }

    // Crée les pôles
    public function addPoles($key, $service)
    {
        $this->pole = new Pole();

        $color = "blue";

        switch ($key) {
            case 3:
                $color = "brown";
                break;
            case 4:
                $color = "orange2";
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
            ->setPole($this->pole)
            ->setCreatedAt(new \DateTime());

        $this->services[] = $this->service;

        $this->manager->persist($this->service);
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

            $this->manager->persist($device);
        }
    }
}
