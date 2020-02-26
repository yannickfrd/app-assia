<?php

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GroupPeopleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use PhpParser\Node\Stmt\Foreach_;

class D_SupportGroupFixtures extends Fixture
{
    private $manager;

    private $user;
    private $service;

    private $groupPeople;

    private $supportGroup, $nbSupports, $startDate, $endDate, $status;
    public $supports;

    public function __construct(EntityManagerInterface $manager, GroupPeopleRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create("fr_FR");
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
            for ($i = 1; $i <= 1; $i++) {
                $this->addSupportGroup($i);

                foreach ($this->groupPeople->getRolePerson() as $rolePerson) {
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

        if ($this->nbSupports >= 2 && $k == 1) {
            $this->status = 2; // 4
            $this->startDate = AppFixtures::getDateTimeBeetwen($this->groupPeople->getCreatedAt(), "now");
            $this->endDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->startDate, "now"));
        } else if ($this->nbSupports >= 2 && $k == 2) {
            $this->status = 2;
            $this->startDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->endDate, "now"));
            $this->endDate = null;
        } else {
            $this->status = 2; // $this->status = mt_rand(2, 4);
            $this->startDate = AppFixtures::getDateTimeBeetwen($this->groupPeople->getCreatedAt(), "now");
            if ($this->status == 4) {
                $this->endDate = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->startDate, "now"));
            } else {
                $this->endDate = null;
            }
        }

        $this->supportGroup->setStartDate($this->startDate)
            ->setEndDate($this->endDate ?? null)
            ->setStatus($this->status)
            ->setReferent($this->user)
            ->setComment(join($this->faker->paragraphs(3)))
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

        $supportPerson = new SupportPerson();

        $supportPerson->setStartDate($this->startDate)
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
