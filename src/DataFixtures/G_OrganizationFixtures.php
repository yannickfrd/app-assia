<?php

namespace App\DataFixtures;

use App\Entity\Organization\Organization;
use App\Repository\Organization\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/*
 * @codeCoverageIgnore
 */
class G_OrganizationFixtures extends Fixture
{
    public const ORGANIZATION = [
        'CCAS',
        'Conseil Départemental',
        'CHRS',
        'SPIP',
        'Autre',
    ];

    protected $userRepo;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepo)
    {
        $this->manager = $manager;
        $this->UserRepo = $userRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $user = $this->UserRepo->findOneBy(['username' => 'r.madelaine']);

        foreach (self::ORGANIZATION as $value) {
            $now = new \Datetime();

            $organization = (new Organization())
                ->setName($value)
                ->setCreatedAt($now)
                ->setCreatedBy($user)
                ->setUpdatedAt($now)
                ->setUpdatedBy($user);

            $this->manager->persist($organization);
        }
        $this->manager->flush();
    }
}
