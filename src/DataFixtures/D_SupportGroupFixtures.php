<?php

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Repository\GroupPeopleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class D_SupportGroupFixtures extends Fixture
{
    private $manager;

    private $user;
    private $service;

    private $groupPeople;

    private $supportGroup;
    private $nbSupports;
    private $startDate;
    private $endDate;
    private $status;
    public $supports;

    public function __construct(EntityManagerInterface $manager, GroupPeopleRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $groupsPeople = $this->repo->findAll();

        foreach ($groupsPeople as $groupPeople) {
            $this->user = $groupPeople->getCreatedBy();
            $this->groupPeople = $groupPeople;

            foreach ($this->user->getServiceUser() as $serviceUser) {
                $this->service = $serviceUser->getService();
            }

            //Crée des faux suivis sociaux
            $this->nbSupports = mt_rand(1, 2);
            for ($i = 1; $i <= 1; ++$i) {
                $this->addSupportGroup($i);

                foreach ($this->groupPeople->getRolePeople() as $rolePerson) {
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

        if ($this->nbSupports >= 2 && 1 == $k) {
            $this->status = 2; // 4
            $this->startDate = AppFixtures::getDateTimeBeetwen($this->groupPeople->getCreatedAt(), 'now');
            $this->endDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->startDate, 'now'));
        } elseif ($this->nbSupports >= 2 && 2 == $k) {
            $this->status = 2;
            $this->startDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->endDate, 'now'));
            $this->endDate = null;
        } else {
            $this->status = 2; // $this->status = mt_rand(2, 4);
            $this->startDate = AppFixtures::getDateTimeBeetwen($this->groupPeople->getCreatedAt(), 'now');
            if (4 == $this->status) {
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
            ->setUpdatedAt($this->groupPeople->getUpdatedAt())
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user)
            ->setGroupPeople($this->groupPeople)
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
            ->setUpdatedAt($this->groupPeople->getUpdatedAt())
            ->setPerson($person)
            ->setSupportGroup($this->supportGroup);

        $this->manager->persist($supportPerson);
    }
}
