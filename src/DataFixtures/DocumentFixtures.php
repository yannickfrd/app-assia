<?php

namespace App\DataFixtures;

use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

/*
 * @codeCoverageIgnore
 */
class DocumentFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $objectManager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        foreach ($objectManager->getRepository((SupportGroup::class))->findAll() as $support) {
            for ($i = 0; $i < mt_rand(5, 10); ++$i) {
                $createdAt = AppFixtures::getDateTimeBeetwen('-12 months', 'now');
                $user = $support->getReferent();

                $document = (new Document())
                ->setName($name = $faker->words(3, true))
                ->setInternalFileName('/documents/'.$createdAt->format('Y/m/d/').strtolower($this->slugger->slug($name)))
                ->setPeopleGroup($support->getPeopleGroup())
                ->setType(mt_rand(1, 10))
                ->setSupportGroup($support)
                ->setCreatedAt($createdAt)
                ->setCreatedBy($user)
                ->setUpdatedAt($createdAt)
                ->setUpdatedBy($user);

                $objectManager->persist($document);
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
        return ['document', 'tag'];
    }
}
