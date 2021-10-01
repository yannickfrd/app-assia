<?php

namespace App\DataFixtures;

use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceUser;
use App\Entity\Organization\User;
use App\Repository\Organization\ServiceRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @codeCoverageIgnore
 */
class B_UserFixtures extends Fixture
{
    private $manager;
    private $serviceRepo;
    private $passwordEncoder;
    private $slugger;
    private $faker;

    public function __construct(
        EntityManagerInterface $manager,
        ServiceRepository $serviceRepo,
        SluggerInterface $slugger,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->manager = $manager;
        $this->serviceRepo = $serviceRepo;
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $this->createSuperAdmin();

        $services = $this->serviceRepo->findAll();

        foreach ($services as $service) {
            for ($i = 1; $i <= 5; ++$i) {
                $this->createUser($service);
            }
        }

        $manager->flush();
    }

    protected function createSuperAdmin()
    {
        $user = new User();
        $user->setUsername('r.madelaine')
            ->setFirstName('Romain')
            ->setLastName('Madelaine')
            ->setStatus(6)
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setPassword($this->passwordEncoder->encodePassword($user, 'Test123'))
            ->setEmail('romain.madelaine@app-assia.org')
            ->setLoginCount(1);

        $this->manager->persist($user);
    }

    public function createUser(Service $service)
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
            ->setPassword($this->passwordEncoder->encodePassword($user, 'Test123'))
            ->setStatus(1)
            ->setEmail($username.'@app-assia.org')
            ->setphone1($phone)
            ->setLoginCount(mt_rand(0, 99))
            ->setLastLogin($lastLogin);

        $this->manager->persist($user);

        $serviceUser = (new ServiceUser())
            ->setUser($user)
            ->setService($service);

        $this->manager->persist($serviceUser);
    }
}
