<?php

namespace App\DataFixtures;

use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Repository\People\PeopleGroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class D_SupportFixtures extends Fixture
{
    private $manager;
    private $peopleGroupRepo;
    private $faker;

    public function __construct(EntityManagerInterface $manager, PeopleGroupRepository $peopleGroupRepo)
    {
        $this->manager = $manager;
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->peopleGroupRepo->findAll() as $peopleGroup) {
            for ($i = 1; $i <= 1; ++$i) {
                $this->createSupportGroup($peopleGroup, $i);
            }
        }
        $this->manager->flush();
    }

    private function createSupportGroup(PeopleGroup $peopleGroup, int $k): ?SupportGroup
    {
        $user = $peopleGroup->getCreatedBy();
        $service = $user->getServices()->first();

        if (!$service) {
            return null;
        }

        $nbSupports = mt_rand(1, 2);
        $endDate = null;

        if ($nbSupports >= 2 && 1 === $k) {
            $status = SupportGroup::STATUS_IN_PROGRESS;
            $startDate = AppFixtures::getDateTimeBeetwen($peopleGroup->getCreatedAt(), 'now');
            $endDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($startDate, 'now'));
        } elseif ($nbSupports >= 2 && 2 === $k) {
            $status = SupportGroup::STATUS_IN_PROGRESS;
            $startDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($endDate, 'now'));
        } else {
            $status = SupportGroup::STATUS_IN_PROGRESS;
            $startDate = AppFixtures::getDateTimeBeetwen($peopleGroup->getCreatedAt(), 'now');
            if (4 === $status) {
                $endDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($startDate, 'now'));
            }
        }

        $supportGroup = (new SupportGroup())
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setStatus($status)
            ->setReferent($user)
            ->setPeopleGroup($peopleGroup)
            ->setNbPeople($peopleGroup->getNbPeople())
            ->setCreatedAt($startDate)
            ->setCreatedBy($user)
            ->setUpdatedAt($peopleGroup->getUpdatedAt())
            ->setUpdatedBy($user)
            ->setService($service);

        $this->manager->persist($supportGroup);

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

        $this->manager->persist($supportPerson);

        return $supportPerson;
    }
}
