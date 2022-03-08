<?php

namespace App\DataFixtures;

use App\Entity\Organization\Tag;
use App\Entity\Support\Document;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class TagFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $objectManager): void
    {
        $tags = [];
        foreach ($this->getDataTags() as $tagName) {
            $tag = (new Tag())->setName($tagName);

            $objectManager->persist($tag);

            $tags[] = $tag;
        }

        $max = count($tags) - 1;

        foreach ($objectManager->getRepository(Document::class)->findAll() as $document) {
            $document->addTag($tags[mt_rand(0, $max)]);
        }

        $objectManager->flush();
    }

    private function getDataTags(): array
    {
        return [
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
    }

    public function getDependencies(): array
    {
        return [
            DocumentFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['tag'];
    }
}
