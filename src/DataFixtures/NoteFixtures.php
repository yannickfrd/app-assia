<?php

namespace App\DataFixtures;

use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class NoteFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $objectManager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        foreach ($objectManager->getRepository(SupportGroup::class)->findAll() as $support) {
            for ($i = 0; $i < mt_rand(5, 10); ++$i) {
                $note = (new Note())
                    ->setTitle($faker->sentence(mt_rand(5, 10), true))
                    ->setContent('<p>'.join('</p><p>', $faker->paragraphs(mt_rand(10, 15))).'</p>')
                    ->setSupportGroup($support)
                    ->setCreatedBy($support->getReferent())
                    ->setUpdatedBy($support->getReferent());

                $objectManager->persist($note);
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
        return ['note'];
    }
}
