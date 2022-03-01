<?php

namespace App\DataFixtures;

use App\Entity\Event\Task;
use App\Entity\Support\SupportGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class TaskFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        foreach ($manager->getRepository(SupportGroup::class)->findAll() as $support) {
            for ($i = 0; $i < mt_rand(6, 10); ++$i) {
                $task = (new Task())
                ->setLevel(mt_rand(1, 3))
                ->setTitle($faker->sentence(mt_rand(3, 5), true))
                ->setEnd(AppFixtures::getDateTimeBeetwen('-3 months', '+3 months'))
                ->setType(1)
                ->setStatus(mt_rand(0, 1))
                ->setSupportGroup($support)
                ->setCreatedBy($support->getReferent())
                ->addUser($support->getReferent());

                $manager->persist($task);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SupportFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['task'];
    }
}
