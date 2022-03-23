<?php

namespace App\DataFixtures;

use App\Entity\Event\Rdv;
use App\Entity\Support\SupportGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class RdvFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $objectManager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        foreach ($objectManager->getRepository(SupportGroup::class)->findAll() as $support) {
            for ($i = 0; $i < mt_rand(5, 10); ++$i) {
                $rdv = (new Rdv())
                ->setTitle($faker->sentence(mt_rand(5, 10), true))
                ->setStart($rdvCreatedAt = AppFixtures::getDateTimeBeetwen('-2 months', '+2 months'))
                ->setEnd($faker->dateTimeInInterval($rdvCreatedAt, '+1 hours'))
                ->setLocation('Cergy-Pontoise')
                ->setSupportGroup($support)
                ->setCreatedAt($rdvCreatedAt)
                ->setCreatedBy($support->getReferent())
                ->setUpdatedAt($rdvCreatedAt)
                ->setUpdatedBy($support->getReferent());

                $objectManager->persist($rdv);
            }
        }
        $objectManager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SupportFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['rdv'];
    }
}
