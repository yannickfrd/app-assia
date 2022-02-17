<?php

namespace App\DataFixtures;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/*
 * @codeCoverageIgnore
 */
class EvaluationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $objectManager;

    /** @var Generator */
    private $faker;

    public function load(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
        $this->faker = \Faker\Factory::create('fr_FR');

        foreach ($objectManager->getRepository(SupportGroup::class)->findAll() as $supportGroup) {
            $this->createEvaluationGroup($supportGroup);
        }
        $objectManager->flush();
    }

    private function createEvaluationGroup(SupportGroup $supportGroup): ?EvaluationGroup
    {
        $evaluationGroup = (new EvaluationGroup())
            ->setBackgroundPeople($this->faker->paragraphs(4, true))
            ->setConclusion($this->faker->paragraphs(3, true))
            ->setSupportGroup($supportGroup)
            ->setCreatedAt($supportGroup->getCreatedAt())
            ->setCreatedBy($supportGroup->getCreatedBy())
        ;

        $this->objectManager->persist($evaluationGroup);

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $this->createEvaluationPerson($evaluationGroup, $supportPerson);
        }

        return $evaluationGroup;
    }

    private function createEvaluationPerson(EvaluationGroup $evaluationGroup, SupportPerson $supportPerson): EvaluationPerson
    {
        $evaluationPerson = (new EvaluationPerson())
            ->setSupportPerson($supportPerson)
            ->setEvaluationGroup($evaluationGroup);

        $this->objectManager->persist($evaluationPerson);

        return $evaluationPerson;
    }

    public function getDependencies(): array
    {
        return [
            SupportFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['evaluation'];
    }
}
