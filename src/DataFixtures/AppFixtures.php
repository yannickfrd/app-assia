<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\GroupPeople;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\SocialSupportGroup;

class AppFixtures extends Fixture
{
    private $manager;

    private $groupPeople;
    private $rolePerson;

    private $familyTypology;
    private $nbPeople;
    private $groupCreatedAt;
    private $groupUpdatedAt;

    private $head;
    private $role;

    private $lastname;
    private $firstname;
    private $birthdate;
    private $sex;

    private $nbSocialSupports;
    private $startDate;
    private $endDate;


    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->faker = \Faker\Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->setTypology();
            $this->addGroupPeople();

            // Crée des fausses personnes pour le ménage
            for ($j = 1; $j <= $this->nbPeople; $j++) {
                $this->familyTypology($j);
                $this->addRolePerson();
                $this->addPerson();
            }

            $this->nbSocialSupports = mt_rand(1, 2);
            for ($k = 1; $k <= $this->nbSocialSupports; $k++) {
                $this->addSocialSupportGroup($k);
            }

            $this->manager->flush();
        }
    }

    // Définit la typologie familiale et le nombre de personnes
    private function setTypology()
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

    // Crée le groupe ménage
    public function addGroupPeople()
    {
        // Définit la date de création
        $this->groupCreatedAt = $this->faker->dateTimeBetween($startDate = "-24 months", $endDate = "now", $timezone = null);
        // Définit la date de mise à jour
        $this->groupUpdatedAt = $this->faker->dateTimeBetween($this->getStartDate($this->groupCreatedAt), $endDate = "now", $timezone = null);

        $this->lastname = $this->faker->lastName();

        $this->groupPeople = new GroupPeople();
        $this->groupPeople->setFamilyTypology($this->familyTypology)
            ->setNbPeople($this->nbPeople)
            ->setComment($this->faker->paragraph())
            ->setCreatedAt($this->groupCreatedAt)
            ->setUpdatedAt($this->groupUpdatedAt);

        $this->manager->persist($this->groupPeople);
    }

    private function getStartDate($date)
    {
        $now = new \DateTime();
        $interval = $now->diff($date);
        $days = $interval->days;
        return "-" . $days . " days";
    }

    // Crée le suivi social du groupe
    public function addSocialSupportGroup($k)
    {
        $socialSupportGroup = new SocialSupportGroup();

        $comment = "<p>" . join($this->faker->paragraphs(3), "</p><p>") . "</p>";

        if ($this->nbSocialSupports >= 2 && $k == 1) {
            $status = 4;
            $this->startDate = $this->faker->dateTimeBetween($this->getStartDate($this->groupCreatedAt), $endDate = "now", $timezone = null);
            $this->endDate = $this->faker->dateTimeBetween($this->getStartDate($this->startDate), $endDate = "now", $timezone = null);
        } else if ($this->nbSocialSupports >= 2 && $k == 2) {
            $status = 2;
            $this->startDate = $this->faker->dateTimeBetween($this->getStartDate($this->endDate), $endDate = "now", $timezone = null);
            $this->endDate = null;
        } else {
            $status = mt_rand(2, 4);
            $this->startDate = $this->faker->dateTimeBetween($this->getStartDate($this->groupCreatedAt), $endDate = "now", $timezone = null);
            if ($status == 4) {
                $this->endDate = $this->faker->dateTimeBetween($this->getStartDate($this->startDate), $endDate = "now", $timezone = null);
            } else {
                $this->endDate = null;
            }
        }

        $socialSupportGroup->setStartDate($this->startDate)
            ->setEndDate($this->endDate ?? null)
            ->setStatus($status)
            ->setComment($comment)
            ->setCreatedAt($this->groupCreatedAt)
            ->setUpdatedAt($this->groupUpdatedAt)
            ->setGroupPeople($this->groupPeople);

        $this->manager->persist($socialSupportGroup);
    }

    // Détermine différentes infos sur la personne en fonction de la typologie familiale
    private function familyTypology($j)
    {
        if ($this->familyTypology == 1) {
            $this->setPerson("adult", 1, true, 5);
        } elseif ($this->familyTypology == 2) {
            $this->setPerson("adult", 2, true, 5);
        } elseif ($this->familyTypology == 3 || $this->familyTypology == 6) {
            if ($j == 1) {
                $this->setPerson("adult", 1, true, 1);
            } elseif ($j == 2) {
                $this->setPerson("adult", 2, false, 1);
            }
        } elseif ($this->familyTypology == 4) {
            if ($j == 1) {
                $this->setPerson("adult", 1, true, 4);
            }
        } elseif ($this->familyTypology == 5) {
            if ($j == 1) {
                $this->setPerson("adult", 2, true, 4);
            }
        }

        if (($this->familyTypology >= 4 && $this->familyTypology <= 5 && $j >= 2) || ($this->familyTypology == 6 && $j >= 3)) {
            $this->setPerson("child", mt_rand(1, 2), false, 3);
        }
    }

    private function setPerson($age, $sex, $head, $role)
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
        $person = new Person();

        $person->setFirstName($this->firstname)
            ->setLastName($this->lastname)
            ->setBirthdate($this->birthdate)
            ->setGender($this->sex)
            ->setEmail($this->faker->freeEmail())
            ->setphone1($this->faker->mobileNumber())
            ->setComment($this->faker->paragraph())
            ->setCreatedAt($this->groupCreatedAt)
            ->setUpdatedAt($this->groupUpdatedAt)
            ->addRolesPerson($this->rolePerson);
        // Prépare le manager à faire persister les données dans le temps
        $this->manager->persist($person);
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
