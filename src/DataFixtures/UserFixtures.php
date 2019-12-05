<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\ServiceUser;
use App\Repository\ServiceRepository;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    private $repoService;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, ServiceRepository $repoService)
    {
        $this->passwordEncoder = $passwordEncoder;
        // $this->repoService = $repoService;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user->setUsername("r.madelaine")
            ->setFirstName("Romain")
            ->setLastName("Madelaine")
            ->setStatus(6)
            ->setRoles("ROLE_SUPER_ADMIN")
            ->setPassword($this->passwordEncoder->encodePassword($user, "test123"))
            ->setEmail("romain.madelaine@esperer-95.org")
            ->setCreatedAt(new \DateTime())
            ->setLoginCount(0)
            ->setLastLogin(new \DateTime());

        $manager->persist($user);

        // $services = $this->repoService->findAll();

        // foreach ($services as $service) {
        //     $serviceUser = new ServiceUser();

        //     $serviceUser->setUser($user)
        //         ->setRole(5)
        //         ->setService($service);

        //     $manager->persist($serviceUser);
        // }

        $manager->flush();
    }
}
