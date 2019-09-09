<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\PeopleGroup;
use App\Entity\Person;
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
            } elseif ($familyTypology == 5) {
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

            $peopleGroup = new PeopleGroup();
            $peopleGroup->setFamilyTypology($familyTypology)
                ->setNbPeople($nbPeople)
                ->setComment($faker->paragraph())
                ->setCreationDate($creationDate)
                ->setUpdateDate($updateDate);

            $manager->persist($peopleGroup); 

            
            // Crée un faux suivi pour le ménage
            $socialSupport = new SocialSupport();

            $comment = "<p>" . join($faker->paragraphs(3),"</p><p>") . "</p>";

            $socialSupport->setBeginningDate($creationDate)
                        ->setStatus(mt_rand(1, 5))
                        ->setComment($comment)
                        ->setCreationDate($creationDate)
                        ->setUpdateDate($updateDate)
                        ->setPeopleGroup($peopleGroup);

            $manager->persist($socialSupport); 

            // Crée des fausses personnes pour le ménage
            for ($j = 1; $j <= $nbPeople; $j++) {

                $person = new Person();

                $sex = mt_rand(1, 2);
                if ($sex == 1) {
                    $firstname = $faker->firstName($gender = "female");
                } else {
                    $firstname = $faker->firstName($gender = "male");
                }

                $person->setFirstName($firstname)
                    ->setLastName($faker->lastName())
                    ->setBirthdate($faker->dateTimeBetween($startDate = '-55 years', $endDate = '-18 years', $timezone = null))
                    ->setGender($sex)
                    ->setmail($faker->freeEmail())
                    ->setphone1($faker->mobileNumber())
                    ->setComment($faker->paragraph())
                    ->setCreationDate($creationDate)
                    ->setUpdateDate($updateDate)
                    ->addPeopleGroup($peopleGroup);

            // Prépare le manager à faire persister les données dans le temps
            $manager->persist($person);
            }
        // Envoie réellement la requête SQL
        $manager->flush();
        }
    }
}