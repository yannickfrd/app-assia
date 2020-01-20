<?php

namespace App\DataFixtures;

use App\Entity\Rdv;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class F_RdvFixtures extends Fixture
{

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager)
    {
        $supports = $this->repo->findAll();

        foreach ($supports as $support) {

            for ($i = 0; $i < mt_rand(6, 12); $i++) {

                $rdv = new Rdv();

                $content = join($this->faker->paragraphs(mt_rand(0, 3)));

                $rdvCreatedAt = AppFixtures::getDateTimeBeetwen("-3 months", "+3 months");
                $rdvUpdatedAt = $rdvCreatedAt;

                $start = $rdvCreatedAt;
                $end = $this->faker->dateTimeInInterval($start, "+1 hours");

                $rdv->setTitle($this->faker->sentence($nbWords = mt_rand(5, 10), $variableNbWords = true))
                    ->setContent($content)
                    ->setStart($start)
                    ->setEnd($end)
                    ->setLocation("Cergy-Pontoise")
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
