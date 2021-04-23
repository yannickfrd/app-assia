<?php

namespace App\DataFixtures;

use App\Entity\Organization\ServiceUser;
use App\Entity\Organization\User;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\ServiceUserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @codeCoverageIgnore
 */
class B_UserFixtures extends Fixture
{
    private $passwordEncoder;
    protected $slugger;
    private $repo;
    private $serviceRepo;

    public $users = [];

    public function __construct(EntityManagerInterface $manager,
    UserPasswordEncoderInterface $passwordEncoder,
    ServiceUserRepository $repo,
    ServiceRepository $serviceRepo)
    {
        $this->manager = $manager;
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = new AsciiSlugger();
        $this->repo = $repo;
        $this->serviceRepo = $serviceRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $serviceUsers = $this->repo->findAll();
        $serviceUser = null;

        foreach ($serviceUsers as $serviceUser) {
            $serviceUser = $serviceUser;
            $this->addUser($serviceUser); // Fixtures
        }

        $this->createSuperAdmin();

        // foreach ($this->getHabitatUsers() as $habitatUser) {

        //     $user = new User();

        //     $username = substr($habitatUser["firstname"], 0, 1) . "." . $habitatUser["lastname"];
        //     $username = strtolower($this->slugger->slug($username));
        //     $password = strtolower($this->slugger->slug($habitatUser["firstname"] . "2502"));

        //     $email = $habitatUser["firstname"] . "." . $habitatUser["lastname"];
        //     $email = strtolower($this->slugger->slug($email) . "@esperer-95.org");

        //     $user->setUsername($username)
        //         ->setFirstName($habitatUser["firstname"])
        //         ->setLastName($habitatUser["lastname"])
        //         ->setStatus(array_key_exists("status", $habitatUser) ? $habitatUser["status"] : 1)
        //         ->setRoles(array_key_exists("roles", $habitatUser) ? [$habitatUser["roles"]] : [])
        //         ->setPassword($this->passwordEncoder->encodePassword($user, "Test123*"))
        //         ->setEmail($email)
        //         ->setCreatedAt(new \DateTime())
        //         ->setUpdatedAt(new \DateTime())
        //         ->setLoginCount(0)
        //         ->setLastLogin(new \DateTime());

        //     $services = $habitatUser["services"];

        //     foreach ($services as $service) {

        //         $service = $this->serviceRepo->findOneBy(["name" => $service]);

        //         $serviceUser = new ServiceUser();

        //         $serviceUser->setRole(1)
        //             ->setService($service);

        //         $manager->persist($serviceUser);

        //         $user->addServiceUser($serviceUser);
        //     }
        //     $manager->persist($user);
        // }
        $manager->flush();
    }

    protected function createSuperAdmin($serviceUser = null)
    {
        $user = new User();
        $now = new \DateTime();

        $user->setUsername('r.madelaine')
            ->setFirstName('Romain')
            ->setLastName('Madelaine')
            ->setStatus(6)
            ->setRoles(['ROLE_SUPER_ADMIN'])
            // ->addServiceUser($serviceUser)
            ->setPassword($this->passwordEncoder->encodePassword($user, 'test123'))
            ->setEmail('romain.madelaine@esperer-95.org')
            ->setLoginCount(1)
            ->setLastLogin($now)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $this->manager->persist($user);
    }

    // Crée les utilisateurs
    public function addUser($serviceUser)
    {
        $user = new User();
        // Définit la date de création et de mise à jour
        $createdAt = AppFixtures::getDateTimeBeetwen('-2 years', '-12 month');
        $lastLogin = AppFixtures::getDateTimeBeetwen('-2 months', 'now');

        $firstname = $this->faker->firstName();
        $lastname = $this->faker->lastName();

        $phone = '01';
        for ($i = 1; $i < 5; ++$i) {
            $phone = $phone.' '.strval(mt_rand(0, 9)).strval(mt_rand(0, 9));
        }

        $user->setUsername($firstname)
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setPassword($this->passwordEncoder->encodePassword($user, 'test2020'))
            ->setStatus(1)
            ->setEmail(mb_strtolower($firstname).'.'.mb_strtolower($lastname).'@esperer-95.org')
            ->setphone1($phone)
            ->setLoginCount(mt_rand(0, 99))
            ->setLastLogin($lastLogin)
            ->addServiceUser($serviceUser);

        $this->users[] = $user;

        $this->manager->persist($user);
    }
}
