<?php

namespace App\DataFixtures;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class SupportFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $objectManager;

    public function load(ObjectManager $objectManager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $this->objectManager = $objectManager;

        foreach ($objectManager->getRepository(PeopleGroup::class)->findAll() as $peopleGroup) {
            for ($i = 1; $i <= 1; ++$i) {
                $this->createSupportGroup($peopleGroup, $i);
            }

            $haveOneDeleted = false;
            foreach ($peopleGroup->getSupports() as $support) {
                if (4 === $support->getStatus() && !$haveOneDeleted) {
                    $haveOneDeleted = true;
                }
            }

            if ($haveOneDeleted && $faker->boolean) {
                $person = $peopleGroup->getPeople()->get(0);

                $familyTypology = mt_rand(1, 6);
                if ($familyTypology <= 2) {
                    $nbPeople = 1;
                } elseif (3 === $familyTypology) {
                    $nbPeople = 2;
                } elseif (6 === $familyTypology) {
                    $nbPeople = mt_rand(3, 6);
                } else {
                    $nbPeople = mt_rand(2, 5);
                }
                $peopleGroup2 = (new PeopleGroup())
                    ->setFamilyTypology($familyTypology)
                    ->setNbPeople($nbPeople)
                    ->setCreatedAt($peopleGroup->getCreatedAt())
                    ->setCreatedBy($peopleGroup->getCreatedBy())
                    ->setUpdatedAt($peopleGroup->getUpdatedAt())
                    ->setUpdatedBy($peopleGroup->getUpdatedBy());
                $objectManager->persist($peopleGroup2);

                $this->createSupportGroup($peopleGroup, 1);

                $rolePerson = (new RolePerson())
                    ->setHead(true)
                    ->setRole(4)
                    ->setPeopleGroup($peopleGroup2)
                    ->setPerson($person);
                $objectManager->persist($rolePerson);
            }
        }

        $objectManager->flush();
    }

    private function createSupportGroup(PeopleGroup $peopleGroup, int $k): ?SupportGroup
    {
        $user = $peopleGroup->getCreatedBy();
        /** @var Service $service */
        $service = $user->getServices()->first();
        /** @var Device $device */
        $device = $service ? $service->getDevices()->first() : null;

        if (!$service) {
            return null;
        }

        $nbSupports = mt_rand(1, 2);
        $endDate = null;

        if ($nbSupports >= 2 && 1 === $k) {
            $status = SupportGroup::STATUS_IN_PROGRESS;
            $startDate = AppFixtures::getDateTimeBeetwen($peopleGroup->getCreatedAt(), 'now');
            $endDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($startDate));
        } elseif ($nbSupports >= 2 && 2 === $k) {
            $status = SupportGroup::STATUS_IN_PROGRESS;
            $startDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($endDate));
        } else {
            $status = SupportGroup::STATUS_IN_PROGRESS;
            $startDate = AppFixtures::getDateTimeBeetwen($peopleGroup->getCreatedAt(), 'now');
            if (4 === $status) {
                $endDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($startDate));
            }
        }

        $supportGroup = (new SupportGroup())
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setStatus($endDate ? SupportGroup::STATUS_ENDED : $status)
            ->setReferent($user)
            ->setPeopleGroup($peopleGroup)
            ->setNbPeople($peopleGroup->getNbPeople())
            ->setAgreement(true)
            ->setCreatedAt($startDate)
            ->setCreatedBy($user)
            ->setUpdatedAt($peopleGroup->getUpdatedAt())
            ->setUpdatedBy($user)
            ->setService($service)
            ->setDevice($device)
        ;

        $this->objectManager->persist($supportGroup);

        $this->objectManager->flush();

        foreach (
            $this->objectManager->getRepository(SupportGroup::class)
                ->findBy(['status' => SupportGroup::STATUS_ENDED]) as $sup
        ) {
            foreach ($peopleGroup->getSupports() as $support) {
                if (
                    $sup->getId() !== $support->getId()
                    && !empty($support->getSupportPeople()->getValues())
                ) {
                    $sup->addSupportPerson($support->getSupportPeople()->getValues()[0]);
                }
            }
        }

        foreach ($peopleGroup->getPeople() as $person) {
            $this->createSupportPerson($supportGroup, $person);
        }

        return $supportGroup;
    }

    private function createSupportPerson(SupportGroup $supportGroup, Person $person): SupportPerson
    {
        $rolePerson = $person->getRolesPerson()->first();

        $supportPerson = (new SupportPerson())
            ->setStartDate($supportGroup->getStartDate())
            ->setEndDate($supportGroup->getEndDate())
            ->setStatus($supportGroup->getStatus())
            ->setHead($rolePerson->getHead())
            ->setRole($rolePerson->getRole())
            ->setCreatedAt($supportGroup->getStartDate())
            ->setUpdatedAt($supportGroup->getUpdatedAt())
            ->setPerson($person)
            ->setSupportGroup($supportGroup);

        $this->objectManager->persist($supportPerson);

        return $supportPerson;
    }

    public function getDependencies(): array
    {
        return [
            PeopleFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['support', 'evaluation', 'note', 'rdv', 'task', 'document', 'payment', 'tag'];
    }
}
