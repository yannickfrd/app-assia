<?php

namespace App\DataFixtures;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
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
    private $em;
    private $peopleGroupRepo;

    public function __construct(EntityManagerInterface $em, PeopleGroupRepository $peopleGroupRepo)
    {
        $this->em = $em;
        $this->peopleGroupRepo = $peopleGroupRepo;
    }

    public function load(ObjectManager $em): void
    {
        foreach ($this->peopleGroupRepo->findAll() as $peopleGroup) {
            for ($i = 1; $i <= 1; ++$i) {
                $this->createSupportGroup($peopleGroup, $i);
            }
        }
        $this->em->flush();
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

        $this->em->persist($supportGroup);

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

        $this->em->persist($supportPerson);

        return $supportPerson;
    }
}
