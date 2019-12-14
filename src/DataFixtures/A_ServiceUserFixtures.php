<?php

namespace App\DataFixtures;

use App\Entity\Pole;
use App\Entity\User;
use App\Entity\Service;
use App\Entity\ServiceUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class A_ServiceUserFixtures extends Fixture
{
    private $manager;

    public const SERVICES_HABITAT = [
        1 => "ALTHO",
        2 => "ASSLT - ASLLT",
        3 => "10 000 logements",
        4 => "SAVL"
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

    private $pole;
    public $poles = [];
    private $service;
    public $services = [];
    private $serviceUser;
    private $passwordEncoder;

    public function __construct(ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder)
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
                case 4:
                    $this->addData($this::SERVICES_HEB);
                    break;
            }
        }
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
        $this->manager->flush();
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
}
