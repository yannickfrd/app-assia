<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\ServiceUser;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ServiceUserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class B_UserFixtures extends Fixture
{
    private $passwordEncoder;
    private $repo;
    private $repoService;

    public $users = [];

    public function __construct(EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder, ServiceUserRepository $repo, ServiceRepository $repoService)
    {
        $this->manager = $manager;
        $this->passwordEncoder = $passwordEncoder;
        $this->repo = $repo;
        $this->repoService = $repoService;
        $this->faker = \Faker\Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager)
    {
        $serviceUsers = $this->repo->findAll();
        $serviceUser = null;

        foreach ($serviceUsers as $serviceUser) {
            $serviceUser = $serviceUser;
            $this->addUser($serviceUser);
        }

        $user = new User();

        $user->setUsername("r.madelaine")
            ->setFirstName("Romain")
            ->setLastName("Madelaine")
            ->setStatus(6)
            ->setRoles(["ROLE_SUPER_ADMIN"])
            ->addServiceUser($serviceUser)
            ->setPassword($this->passwordEncoder->encodePassword($user, "test123"))
            ->setEmail("romain.madelaine@esperer-95.org")
            ->setActive(true)
            ->setLoginCount(0)
            ->setLastLogin(new \DateTime())
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $manager->persist($user);

        foreach ($this->getHabitatUsers() as $habitatUser) {

            $user = new User();

            $username = substr($habitatUser["firstname"], 0, 1) . "." . $habitatUser["lastname"];
            $username = transliterator_transliterate("Any-Latin; Latin-ASCII; [^A-Za-z0-9_.] remove; Lower()", $username) . "_test";

            $email = $habitatUser["firstname"] . "." . $habitatUser["lastname"];
            $email = transliterator_transliterate("Any-Latin; Latin-ASCII; [^A-Za-z0-9_.] remove; Lower()", $email) . "@esperer-95.org";

            $user->setUsername($username)
                ->setFirstName($habitatUser["firstname"])
                ->setLastName($habitatUser["lastname"])
                ->setStatus(array_key_exists("status", $habitatUser) ? $habitatUser["status"] : 1)
                ->setRoles(array_key_exists("roles", $habitatUser) ? [$habitatUser["roles"]] : [])
                ->setPassword($this->passwordEncoder->encodePassword($user, "test2020"))
                ->setEmail($email)
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime())
                ->setActive(true)
                ->setLoginCount(0)
                ->setLastLogin(new \DateTime());

            $services = $habitatUser["services"];

            foreach ($services as $service) {

                $service = $this->repoService->findOneBy(["name" => $service]);

                $serviceUser = new ServiceUser();

                $serviceUser->setRole(1)
                    ->setService($service);

                $manager->persist($serviceUser);

                $user->addServiceUser($serviceUser);
            }
            $manager->persist($user);
        }
        $manager->flush();
    }

    // Crée les utilisateurs
    public function addUser($serviceUser)
    {
        $user = new User();
        // Définit la date de création et de mise à jour
        $createdAt = AppFixtures::getDateTimeBeetwen("-2 years", "-12 month");
        $lastLogin = AppFixtures::getDateTimeBeetwen("-2 months", "now");

        $firstname = $this->faker->firstName();
        $lastname = $this->faker->lastName();

        $phone = "01";
        for ($i = 1; $i < 5; $i++) {
            $phone  = $phone  . " " . strval(mt_rand(0, 9)) . strval(mt_rand(0, 9));
        }

        $user->setUsername($firstname)
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setPassword($this->passwordEncoder->encodePassword($user, "test123"))
            ->setStatus(1)
            ->setEmail(mb_strtolower($firstname) . "." . mb_strtolower($lastname) . "@esperer-95.org")
            ->setphone($phone)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt)
            ->setLoginCount(mt_rand(0, 99))
            ->setLastLogin($lastLogin)
            ->addServiceUser($serviceUser);

        $this->users[] = $user;

        $this->manager->persist($user);
    }


    public function getHabitatUsers()
    {
        return [
            0 => [
                "firstname" => "Gaëlle",
                "lastname" => "ARTIFONI",
                "status" => 4,
                "roles" => "ROLE_ADMIN",
                "services" => [
                    "ALTHO",
                    "ASSLT - ASLLT",
                    "10 000 logements",
                    "SAVL",
                    "AVDL"
                ]
            ],
            1 => [
                "firstname" => "Laetitia",
                "lastname" => "CHANIAL",
                "status" => 3,
                "roles" => "ROLE_ADMIN",
                "services" => [
                    "ALTHO",
                    "ASSLT - ASLLT",
                    "10 000 logements",
                    "SAVL",
                    "AVDL"
                ]
            ],
            2 => [
                "firstname" => "Priscillia",
                "lastname" => "CORNU",
                "status" => 1,
                "services" => [
                    "ALTHO"
                ]
            ],
            3 => [
                "firstname" => "Krystel",
                "lastname" => "FONDRILLE",
                "services" => [
                    "ASSLT - ASLLT",
                ]
            ],
            4 => [
                "firstname" => "Aurore",
                "lastname" => "FROMONT",
                "status" => 2,
                "roles" => "ROLE_ADMIN",
                "services" => [
                    "ALTHO",
                    "ASSLT - ASLLT",
                    "10 000 logements",
                    "SAVL",
                    "AVDL"
                ]
            ],
            5 => [
                "firstname" => "Nicolas",
                "lastname" => "GIROD",
                "services" => [
                    "10 000 logements",
                ]
            ],
            6 => [
                "firstname" => "Lucie",
                "lastname" => "LALOU",
                "services" => [
                    "ASSLT - ASLLT",
                    "SAVL"
                ]
            ],
            7 => [
                "firstname" => "Valerie",
                "lastname" => "LEOTARD",
                "services" => [
                    "ALTHO"
                ]
            ],
            8 => [
                "firstname" => "Melanie",
                "lastname" => "LEY",
                "services" => [
                    "SAVL",
                ]
            ],
            9 => [
                "firstname" => "Eva",
                "lastname" => "MASSON",
                "services" => [
                    "AVDL",
                ]
            ],
            10 => [
                "firstname" => "Michaël",
                "lastname" => "ORPHELIN",
                "services" => [
                    "AVDL",
                ]
            ],
            11 => [
                "firstname" => "Alison",
                "lastname" => "ROUGIER",
                "status" => 5,
                "services" => [
                    "AVDL",
                ]
            ],
            12 => [
                "firstname" => "Maryse",
                "lastname" => "STEPHAN",
                "services" => [
                    "ALTHO",
                ]
            ],
            13 => [
                "firstname" => "Kristell",
                "lastname" => "VIMOND",
                "services" => [
                    "AVDL",
                ]
            ],
        ];
    }
}
