<?php

namespace App\DataFixtures;

use App\Entity\Organization\User;
use App\Entity\Organization\Service;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Organization\ServiceUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Repository\Organization\ServiceRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class B_UserFixtures extends Fixture
{
    private $em;
    private $serviceRepo;
    private $passwordHasher;
    private $slugger;
    private $faker;

    public function __construct(
        EntityManagerInterface $em,
        ServiceRepository $serviceRepo,
        SluggerInterface $slugger,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->em = $em;
        $this->serviceRepo = $serviceRepo;
        $this->passwordHasher = $passwordHasher;
        $this->slugger = $slugger;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $em)
    {
        $this->createSuperAdmin();

        $services = $this->serviceRepo->findAll();

        foreach ($services as $service) {
            for ($i = 1; $i <= 5; ++$i) {
                $this->createUser($service);
            }
        }

        $this->createDefaultUser($services[0]);

        $em->flush();
    }

    protected function createSuperAdmin(): void
    {
        $user = new User();
        $user->setUsername('y.farade')
            ->setFirstName('Yannick')
            ->setLastName('Farade')
            ->setStatus(6)
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($user, 'azerty'))
            ->setEmail('yannick.farade@app-assia.org')
            ->setLoginCount(1);

        $this->em->persist($user);
    }

    protected function createDefaultUser(Service $service): void
    {
        $user = new User();
        $user->setUsername('user_test')
            ->setFirstName('Test')
            ->setLastName('Test')
            ->setStatus(1)
            ->setPassword($this->passwordHasher->hashPassword($user, 'test123'))
            ->setEmail('test@app-assia.org')
            ->setLoginCount(0);

        $this->em->persist($user);

        $serviceUser = (new ServiceUser())
            ->setUser($user)
            ->setService($service);

        $this->em->persist($serviceUser);
    }

    protected function createUser(Service $service): void
    {
        $user = new User();
        $lastLogin = AppFixtures::getDateTimeBeetwen('-2 months', 'now');

        $firstname = $this->faker->firstName();
        $lastname = $this->faker->lastName();
        $username = strtolower($this->slugger->slug($firstname).'.'.$this->slugger->slug($lastname));

        $phone = '01';
        for ($i = 1; $i < 5; ++$i) {
            $phone = $phone.' '.strval(mt_rand(0, 9)).strval(mt_rand(0, 9));
        }

        $user
            ->setUsername($username)
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setPassword($this->passwordHasher->hashPassword($user, 'Test123'))
            ->setStatus(1)
            ->setEmail($username.'@app-assia.org')
            ->setphone1($phone)
            ->setLoginCount(mt_rand(0, 99))
            ->setLastLogin($lastLogin);

        $this->em->persist($user);

        $serviceUser = (new ServiceUser())
            ->setUser($user)
            ->setService($service);

        $this->em->persist($serviceUser);
    }
}
