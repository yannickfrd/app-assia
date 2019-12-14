<?php

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class C_GroupPeopleFixtures extends Fixture
{
    private $manager;

    private $user;

    private $groupPeople, $familyTypology, $nbPeople, $groupCreatedAt, $groupUpdatedAt;
    private $rolePerson, $head, $role;
    private $person, $lastname, $firstname, $birthdate, $sex;

    public function __construct(ObjectManager $manager, UserRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager)
    {
        $users = $this->repo->findAll();

        foreach ($users as $user) {
            $this->user = $user;

            // Crée des faux groupes
            for ($i = 1; $i <= mt_rand(10, 15); $i++) {
                $this->setTypology();
                $this->addGroupPeople();

                // Crée des fausses personnes dans le groupe
                for ($j = 1; $j <= $this->nbPeople; $j++) {
                    $this->familyTypology($j);
                    $this->addRolePerson();
                    $this->addPerson();
                    // $this->addSupportPerson();
                }
            }
        }
        $this->manager->flush();
    }

    // Définit la typologie familiale et le nombre de personnes
    protected function setTypology()
    {
        // Définit la typologie familiale
        $this->familyTypology = mt_rand(1, 6);
        if ($this->familyTypology <= 2) {
            $this->nbPeople = 1;
        } elseif ($this->familyTypology == 3) {
            $this->nbPeople = 2;
        } elseif ($this->familyTypology == 6) {
            $this->nbPeople = mt_rand(3, 6);
        } else {
            $this->nbPeople = mt_rand(2, 5);
        }
    }

    // Crée le groupe
    public function addGroupPeople()
    {
        // Définit la date de création et de mise à jour
        $this->groupCreatedAt = AppFixtures::getDateTimeBeetwen("-24 months", "now");
        $this->groupUpdatedAt = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->groupCreatedAt), "now");

        $this->lastname = $this->faker->lastName();

        $this->groupPeople = new GroupPeople();
        $this->groupPeople->setFamilyTypology($this->familyTypology)
            ->setNbPeople($this->nbPeople)
            ->setCreatedAt($this->groupCreatedAt)
            ->setCreatedBy($this->user)
            ->setUpdatedAt($this->groupUpdatedAt)
            ->setUpdatedBy($this->user);

        $this->manager->persist($this->groupPeople);
    }

    // Détermine différentes infos sur la personne en fonction de la typologie familiale
    protected function familyTypology($l)
    {
        if ($this->familyTypology == 1) {
            $this->setPerson("adult", 1, true, 5);
        } elseif ($this->familyTypology == 2) {
            $this->setPerson("adult", 2, true, 5);
        } elseif ($this->familyTypology == 3 || $this->familyTypology == 6) {
            if ($l == 1) {
                $this->setPerson("adult", 1, true, 1);
            } elseif ($l == 2) {
                $this->setPerson("adult", 2, false, 1);
            }
        } elseif ($this->familyTypology == 4) {
            if ($l == 1) {
                $this->setPerson("adult", 1, true, 4);
            }
        } elseif ($this->familyTypology == 5) {
            if ($l == 1) {
                $this->setPerson("adult", 2, true, 4);
            }
        }

        if (($this->familyTypology >= 4 && $this->familyTypology <= 5 && $l >= 2) || ($this->familyTypology == 6 && $l >= 3)) {
            $this->setPerson("child", mt_rand(1, 2), false, 3);
        }
    }

    protected function setPerson($age, $sex, $head, $role)
    {
        $this->firstname = $this->faker->firstName($sex == 1 ? "female" : "male");
        $this->birthdate = $this->birthdate($age);
        $this->sex = $sex;
        $this->head = $head;
        $this->role = $role;
    }

    // Crée le rôle de la personne dans le groupe
    public function addRolePerson()
    {
        $this->rolePerson = new RolePerson();

        $this->rolePerson->setHead($this->head)
            ->setRole($this->role)
            ->setGroupPeople($this->groupPeople)
            ->setCreatedAt($this->groupCreatedAt);

        $this->manager->persist($this->rolePerson);
    }

    // Crée la personne
    public function addPerson()
    {
        $this->person = new Person();
        $this->firstname = $this->faker->firstName();

        $phone = "06";
        for ($i = 1; $i < 5; $i++) {
            $phone  = $phone  . " " . strval(mt_rand(0, 9)) . strval(mt_rand(0, 9));
        }

        $this->person->setFirstName($this->firstname)
            ->setLastName($this->lastname)
            ->setBirthdate($this->birthdate)
            ->setGender($this->sex)
            ->setEmail($this->faker->freeEmail())
            ->setphone1($phone)
            ->setCreatedAt($this->groupCreatedAt)
            ->setUpdatedAt($this->groupUpdatedAt)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user)
            ->addRolesPerson($this->rolePerson);
        // Prépare le manager à faire persister les données dans le temps
        $this->manager->persist($this->person);
    }

    // Donne une date de naissanc en fonction du role de la personne
    protected function birthdate($role = "adult")
    {
        if ($role == "adult") {
            $birthdate = $this->faker->dateTimeBetween($startDate = "-55 years", $endDate = "-18 years", $timezone = null);
        } else {
            $birthdate = $this->faker->dateTimeBetween($startDate = "-18 years", $endDate = "now", $timezone = null);
        }
        return $birthdate;
    }
}
