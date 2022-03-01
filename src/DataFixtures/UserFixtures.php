<?php

namespace App\DataFixtures;

use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceUser;
use App\Entity\Organization\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @codeCoverageIgnore
 */
class UserFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $slugger;
    private $passwordHasher;
    private $objectManager;

    public function __construct(SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher)
    {
        $this->slugger = $slugger;
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
        $faker = \Faker\Factory::create('fr_FR');

        $services = $this->objectManager->getRepository(Service::class)->findAll();
        $password = $this->passwordHasher->hashPassword(new User(), 'password');

        $this->addReference('service_chu', $services[0]);
        $this->addReference('service_pash', $services[6]);

        foreach ($services as $service) {
            for ($i = 1; $i <= 5; ++$i) {
                $firstname = $faker->firstName();
                $lastname = $faker->lastName();
                $username = strtolower(substr($this->slugger->slug($firstname), 0, 1).'.'.$this->slugger->slug($lastname));
                $this->createUser($username, $password, $firstname, $lastname, 1, ['ROLE_USER'], [$service]);
            }
        }

        foreach ($this->getDataUsers() as [$username, $firstname, $lastname, $status, $roles, $services]) {
            $this->createUser($username, $password, $firstname, $lastname, $status, $roles, $services);
        }

        $this->objectManager->flush();
    }

    private function createUser(string $username, string $password, string $firstname, string $lastname,
        int $status, array $roles = [], array $services = []): void
    {
        $phone = '01';
        for ($i = 1; $i < 5; ++$i) {
            $phone .= ' '.strval(mt_rand(0, 9)).strval(mt_rand(0, 9));
        }

        $user = new User();
        $user->setUsername($username)
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setStatus($status)
            ->setRoles($roles)
            ->setphone1($phone)
            ->setPassword($password)
            ->setEmail($username.'@app-assia.org')
            ->setLoginCount(mt_rand(0, 99));

        $this->objectManager->persist($user);

        if ($services) {
            foreach ($services as $service) {
                $serviceUser = (new ServiceUser())
                ->setUser($user)
                ->setService($service);

                $this->objectManager->persist($serviceUser);
            }
        }
    }

    private function getDataUsers(): array
    {
        return [
            // $userData = [$firstname, $lastname, $status, $roles, $services]
            ['tom_super_admin', 'Tom', 'SUPER ADMIN', 6, ['ROLE_SUPER_ADMIN'], []],
            ['john_user', 'John', 'DOE', 1, ['ROLE_USER'], [$this->getReference('service_chu')]],
            ['jane_user', 'Jane', 'DOE', 1, ['ROLE_USER'], [$this->getReference('service_pash')]],
            ['tom_admin', 'Tom', 'DOE', 3, ['ROLE_ADMIN'], [
                $this->getReference('service_chu'),
                $this->getReference('service_pash'),
            ]],
        ];
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['user', 'people', 'support', 'evaluation', 'note', 'rdv', 'task', 'document', 'payment', 'tag'];
    }
}
