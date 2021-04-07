<?php

namespace App\DataFixtures;

use App\Entity\People\Person;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Repository\People\PeopleGroupRepository;

/*
 * @codeCoverageIgnore
 */
class D_SupportGroupFixtures extends Fixture
{
    private $manager;

    private $user;
    private $service;

    private $peopleGroup;

    private $supportGroup;
    private $nbSupports;
    private $startDate;
    private $endDate;
    private $status;
    public $supports;

    public function __construct(EntityManagerInterface $manager, PeopleGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $peopleGroups = $this->repo->findAll();

        foreach ($peopleGroups as $peopleGroup) {
            $this->user = $peopleGroup->getCreatedBy();
            $this->peopleGroup = $peopleGroup;

            foreach ($this->user->getServices() as $service) {
                $this->service = $service;
            }

            //Crée des faux suivis sociaux
            $this->nbSupports = mt_rand(1, 2);
            for ($i = 1; $i <= 1; ++$i) {
                $this->addSupportGroup($i);

                foreach ($this->peopleGroup->getRolePeople() as $rolePerson) {
                    $this->addSupportPerson($rolePerson->getPerson());
                }
            }
        }
        $this->manager->flush();
    }

    // Crée le suivi social du groupe
    public function addSupportGroup($k)
    {
        $this->supportGroup = new SupportGroup();

        if ($this->nbSupports >= 2 && 1 === $k) {
            $this->status = SupportGroup::STATUS_IN_PROGRESS;
            $this->startDate = AppFixtures::getDateTimeBeetwen($this->peopleGroup->getCreatedAt(), 'now');
            $this->endDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->startDate, 'now'));
        } elseif ($this->nbSupports >= 2 && 2 === $k) {
            $this->status = SupportGroup::STATUS_IN_PROGRESS;
            $this->startDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->endDate, 'now'));
            $this->endDate = null;
        } else {
            $this->status = SupportGroup::STATUS_IN_PROGRESS;
            $this->startDate = AppFixtures::getDateTimeBeetwen($this->peopleGroup->getCreatedAt(), 'now');
            if (4 === $this->status) {
                $this->endDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->startDate, 'now'));
            } else {
                $this->endDate = null;
            }
        }

        $this->supportGroup->setStartDate($this->startDate)
            ->setEndDate($this->endDate ?? null)
            ->setStatus($this->status)
            ->setReferent($this->user)
            ->setCreatedAt($this->startDate)
            ->setUpdatedAt($this->peopleGroup->getUpdatedAt())
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user)
            ->setPeopleGroup($this->peopleGroup)
            ->setService($this->service);

        $this->manager->persist($this->supportGroup);
    }

    // Crée le suivi social du groupe
    public function addSupportPerson(Person $person)
    {
        $rolePerson = null;
        foreach ($person->getRolesPerson() as  $role) {
            $rolePerson = $role;
        }

        $supportPerson = (new SupportPerson())
            ->setStartDate($this->startDate)
            ->setEndDate($this->endDate ?? null)
            ->setStatus($this->status)
            ->setHead($rolePerson->getHead())
            ->setRole($rolePerson->getRole())
            ->setCreatedAt($this->startDate)
            ->setUpdatedAt($this->peopleGroup->getUpdatedAt())
            ->setPerson($person)
            ->setSupportGroup($this->supportGroup);

        $this->manager->persist($supportPerson);
    }
}
