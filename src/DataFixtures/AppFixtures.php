<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Person;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager) {
        for ($i = 1; $i <=20; $i++) {
            $person = new PersonShow();
            $person->setFirstName("Nom $i")
                ->setLastName("Prénom $i")
                ->setCreationDate(new \DateTime())
                ->setUpdateDate(new \DateTime());
        
        // Prépare le manager à faire persister les données dans le temps
        $manager->persist($person); 
        }
        // Envoie réellement la requête SQL
        $manager->flush();
    }
}