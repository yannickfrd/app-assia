<?php

namespace App\DataFixtures;

use App\Entity\Organization\Organization;
use App\Repository\Organization\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/*
 * @codeCoverageIgnore
 */
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
