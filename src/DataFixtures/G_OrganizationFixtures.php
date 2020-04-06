<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class G_OrganizationFixtures extends Fixture
{
    public const ORGANIZATION = [
        'CCAS',
        'Conseil Départemental',
        'ESPERER 95 - CHRS Hermitage',
        'ESPERER 95 - PE 95',
        'ESPERER 95 - PE 78',
        'ESPERER 95 - Pré-sententiel',
        'ESPERER 95 - Autre',
        'SPIP',
        'Autre',
    ];

    protected $repoUser;

    public function __construct(EntityManagerInterface $manager, UserRepository $repoUser)
    {
        $this->manager = $manager;
        $this->repoUser = $repoUser;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $user = $this->repoUser->findOneBy(['username' => 'r.madelaine']);

        foreach (self::ORGANIZATION as $value) {
            $organization = new Organization();

            $now = new \Datetime();

            $organization->setName($value)
                ->setCreatedAt($now)
                ->setCreatedBy($user)
                ->setUpdatedAt($now)
                ->setUpdatedBy($user);

            $this->manager->persist($organization);
        }
        $this->manager->flush();
    }
}
