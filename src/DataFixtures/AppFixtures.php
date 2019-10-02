<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\GroupPeople;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\SocialSupport;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager) {

        $faker = \Faker\Factory::create("fr_FR");

        for ($i = 1; $i <= 20; $i++) {
            // Définit la typologie familiale
            $familyTypology = mt_rand(1, 6);
            // Définit le nombre de personnes
            if ($familyTypology <= 2) {
                $nbPeople = 1;
            } elseif ($familyTypology == 3) {
                $nbPeople = 2;
            } elseif ($familyTypology == 6) {
                $nbPeople = mt_rand(3, 6);
            } else {
                $nbPeople = mt_rand(2, 5);
            }

            // Définit la date de création
            $creationDate = $faker->dateTimeBetween($startDate = "-12 months", $endDate = "now", $timezone = null);
            $now = new \DateTime();
            $interval = $now->diff($creationDate);
            $days = $interval->days;
            $min = "-" . $days . " days";
            // Définit la date de mise à jour
            $updateDate = $faker->dateTimeBetween($min, $endDate = "now", $timezone = null);

            $groupPeople = new GroupPeople();
            $groupPeople->setFamilyTypology($familyTypology)
                ->setNbPeople($nbPeople)
                ->setComment($faker->paragraph())
                ->setCreationDate($creationDate)
                ->setUpdateDate($updateDate);

            $manager->persist($groupPeople); 

            
            // Crée un faux suivi pour le ménage
            $socialSupport = new SocialSupport();

            $comment = "<p>" . join($faker->paragraphs(3),"</p><p>") . "</p>";

            $socialSupport->setBeginningDate($creationDate)
                        ->setStatus(mt_rand(1, 4))
                        ->setComment($comment)
                        ->setCreationDate($creationDate)
                        ->setUpdateDate($updateDate)
                        ->setGroupPeople($groupPeople);

            $manager->persist($socialSupport); 

            $lastname = $faker->lastName();

            // Crée des fausses personnes pour le ménage
            for ($j = 1; $j <= $nbPeople; $j++) {

                $rolePerson = new RolePerson();

                $person = new Person();

                if ($familyTypology == 1) {
                    $firstname = $faker->firstName($gender = "female");
                    $birthdate = $this->birthdate("adult");
                    $sex = 1;
                    $head = true;
                    $role = 1;
                } elseif ($familyTypology == 2) {
                    $firstname = $faker->firstName($gender = "male");
                    $birthdate = $this->birthdate("adult");
                    $sex = 2;
                    $head = true;
                    $role = 1;
                } elseif ($familyTypology == 3 || $familyTypology == 6) {
                    if ($j == 1) {
                        $firstname = $faker->firstName($gender = "female");
                        $birthdate = $this->birthdate("adult");
                        $sex = 1;
                        $head = true;
                        $role = 1;
                    } elseif ($j == 2) {
                        $firstname = $faker->firstName($gender = "male");
                        $birthdate = $this->birthdate("adult");
                        $sex = 2;
                        $head = false;
                        $role = 2;
                    }
                } elseif ($familyTypology == 4) {
                    if ($j == 1) {
                        $firstname = $faker->firstName($gender = "female");
                        $birthdate = $this->birthdate("adult");
                        $sex = 1;
                        $head = true;
                        $role = 1;
                    }
                } elseif ($familyTypology == 5) {
                    if ($j == 1) {
                        $firstname = $faker->firstName($gender = "male");
                        $birthdate = $this->birthdate("adult");
                        $sex = 2;
                        $head = true;
                        $role = 1;
                    }
                }

                if (($familyTypology >= 4 && $familyTypology <= 5 && $j >=2) || ($familyTypology == 6 && $j >=3)) {
                    $birthdate = $this->birthdate("child");
                    $sex = mt_rand(1, 2);
                    $head = false;
                    $role = 4;
                    if ($sex == 1) {
                        $firstname = $faker->firstName($gender = "female");
                    } else {
                        $firstname = $faker->firstName($gender = "male");
                    }
                }

                $rolePerson->setHead($head)
                        ->setRole($role)
                        ->setGroupPeople($groupPeople);

                $manager->persist($rolePerson);

                $person->setFirstName($firstname)
                    ->setLastName($lastname)
                    ->setBirthdate($birthdate)
                    ->setGender($sex)
                    ->setmail($faker->freeEmail())
                    ->setphone1($faker->mobileNumber())
                    ->setComment($faker->paragraph())
                    ->setCreationDate($creationDate)
                    ->setUpdateDate($updateDate)
                    ->addRolesPerson($rolePerson);
                // Prépare le manager à faire persister les données dans le temps
                $manager->persist($person);

            }
        // Envoie la requête SQL
        $manager->flush();
        }
    }

    // Donne une date de naissanc en fonction du role de la personne
    protected function birthdate($role = "adult") {

        $faker = \Faker\Factory::create("fr_FR");

        if ($role == "adult") {
            $birthdate = $faker->dateTimeBetween($startDate = "-55 years", $endDate = "-18 years", $timezone = null);
        } else {
            $birthdate = $faker->dateTimeBetween($startDate = "-18 years", $endDate = "now", $timezone = null);
        }
        return $birthdate;
    }

}