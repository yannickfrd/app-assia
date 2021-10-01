<?php

namespace App\DataFixtures;

use App\Entity\Support\Rdv;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class E_RdvFixtures extends Fixture
{
    private $manager;
    private $supportGroupRepo;
    private $faker;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $supportGroupRepo)
    {
        $this->manager = $manager;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->supportGroupRepo->findAll() as $support) {
            for ($i = 0; $i < mt_rand(6, 10); ++$i) {
                $rdv = (new Rdv())
                ->setTitle($this->faker->sentence(mt_rand(5, 10), true))
                ->setStart($rdvCreatedAt = AppFixtures::getDateTimeBeetwen('-2 months', '+2 months'))
                ->setEnd($this->faker->dateTimeInInterval($rdvCreatedAt, '+1 hours'))
                ->setLocation('Cergy-Pontoise')
                ->setSupportGroup($support)
                ->setCreatedAt($rdvCreatedAt)
                ->setCreatedBy($support->getReferent())
                ->setUpdatedAt($rdvCreatedAt)
                ->setUpdatedBy($support->getReferent());

                $this->manager->persist($rdv);
            }
        }
        $this->manager->flush();
    }
}
