<?php

namespace App\DataFixtures;

use App\Entity\Rdv;
use App\Repository\SupportGroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class F_RdvFixtures extends Fixture
{
    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $supports = $this->repo->findAll();

        foreach ($supports as $support) {
            for ($i = 0; $i < mt_rand(6, 10); ++$i) {
                $rdv = new Rdv();

                $rdvCreatedAt = AppFixtures::getDateTimeBeetwen('-2 months', '+2 months');
                $rdvUpdatedAt = $rdvCreatedAt;

                $start = $rdvCreatedAt;
                $end = $this->faker->dateTimeInInterval($start, '+1 hours');

                $rdv->setTitle($this->faker->sentence($nbWords = mt_rand(5, 10), $variableNbWords = true))
                    ->setStart($start)
                    ->setEnd($end)
                    ->setLocation('Cergy-Pontoise')
                    ->setSupportGroup($support)
                    ->setCreatedAt($rdvCreatedAt)
                    ->setCreatedBy($support->getReferent())
                    ->setUpdatedAt($rdvUpdatedAt)
                    ->setUpdatedBy($support->getReferent());

                $this->manager->persist($rdv);
            }
        }
        $this->manager->flush();
    }
}
