<?php

namespace App\DataFixtures;

use App\Entity\User;

use App\Repository\ServiceUserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class B_UserFixtures extends Fixture
{
    private $passwordEncoder;
    private $repo;

    public $users = [];

    public function __construct(ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder, ServiceUserRepository $repo)
    {
        $this->manager = $manager;
        $this->passwordEncoder = $passwordEncoder;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager)
    {
        $serviceUsers = $this->repo->findAll();
        $serviceUser = null;

        foreach ($serviceUsers as $serviceUser) {
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
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setLoginCount(0)
            ->setLastLogin(new \DateTime());

        $manager->persist($user);


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
}
