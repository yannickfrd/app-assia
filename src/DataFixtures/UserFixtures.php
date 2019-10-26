<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        // $faker = \Faker\Factory::create("fr_FR");

        // for ($i = 1; $i <= 10; $i++) {

        //     $user = new User();

        //     // Définit la date de création
        //     $createdAt = $faker->dateTimeBetween($startDate = "-12 months", $endDate = "now", $timezone = null);
        //     $now = new \DateTime();
        //     $interval = $now->diff($createdAt);
        //     $days = $interval->days;
        //     $min = "-" . $days . " days";
        //     // Définit la date de mise à jour
        //     $lastLogin = $faker->dateTimeBetween($min = "-2 months", $endDate = "now", $timezone = null);

        //     $firstname = $faker->firstName();

        //     $user->setUsername($firstname)
        //         ->setFirstName($firstname)
        //         ->setLastName($faker->lastName())
        //         ->setPassword($this->passwordEncoder->encodePassword($user, "test123"))
        //         ->setEmail($faker->freeEmail())
        //         ->setCreatedAt($createdAt)
        //         ->setLoginCount(mt_rand(0, 99))
        //         ->setLastLogin($lastLogin);

        //     $manager->persist($user);
        // }

        $user = new User();

        $user->setUsername("Romain")
            ->setFirstName("Romain")
            ->setLastName("Madelaine")
            ->setPassword($this->passwordEncoder->encodePassword($user, "test123"))
            ->setEmail("romain.madelaine@gmail.com")
            ->setCreatedAt(new \DateTime())
            ->setLoginCount(0)
            ->setLastLogin(new \DateTime());

        $manager->persist($user);

        $manager->flush();
    }
}
