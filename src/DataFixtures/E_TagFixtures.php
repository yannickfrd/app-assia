<?php

namespace App\DataFixtures;

use App\Entity\Organization\Tag;
use App\Entity\Support\Document;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/*
 * @codeCoverageIgnore
 */
class E_TagFixtures extends Fixture
{
    private $manager;
    private $faker;
    private $createdAt;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->faker = Factory::create('fr_FR');
        $this->createdAt = AppFixtures::getDateTimeBeetwen('-12 months');
    }

    public function load(ObjectManager $manager): void
    {
        $defaultTags = [
            1 => 'Identité/Etat civil',
            2 => 'Administratif',
            3 => 'Ressources',
            4 => 'Impôts',
            5 => 'Redevance',
            6 => 'Logement',
            7 => 'Santé',
            8 => 'Orientation',
            9 => 'Emploi',
            10 => 'Dettes',
            97 => 'Autre',
        ];

        $documents = $this->manager->getRepository(Document::class)->findAll();

//        foreach ($defaultTags as $tagName) {
//            $tag = (new Tag())
//                ->setName($tagName)
//                ->setCreatedAt($this->createdAt)
//                ->setUpdatedAt($this->createdAt);
//
//
//            if ($this->faker->boolean) {
//                for ($i = 0; $i < mt_rand(6, count($documents)); ++$i) {
//                    if ($this->faker->boolean) {
//                        $tag->addDocument($documents[$i]);
//                    }
//                }
//            }
//
//            $this->manager->persist($tag);
//        }
//        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            E_DocumentFixtures::class,
        ];
    }
}
