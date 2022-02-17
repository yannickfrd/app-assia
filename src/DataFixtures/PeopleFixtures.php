<?php

namespace App\DataFixtures;

use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class PeopleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $objectManager;
    private $faker;

    private $head;
    private $role;
    private $firstname;
    private $birthdate;
    private $sex;

    public function load(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
        $this->faker = \Faker\Factory::create('fr_FR');

        foreach ($objectManager->getRepository(User::class)->findAll() as $user) {
            // Crée des faux groupes
            for ($i = 1; $i <= mt_rand(5, 10); ++$i) {
                $this->createPeopleGroup($user);
            }
        }
        $this->objectManager->flush();
    }

    // Crée le groupe
    private function createPeopleGroup(User $user): void
    {
        // Définit la date de création et de mise à jour
        $createdAt = AppFixtures::getDateTimeBeetwen('-24 months', 'now');
        $updatedAt = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($createdAt), 'now');

        $familyTypology = mt_rand(1, 6);
        if ($familyTypology <= 2) {
            $nbPeople = 1;
        } elseif (3 === $familyTypology) {
            $nbPeople = 2;
        } elseif (6 === $familyTypology) {
            $nbPeople = mt_rand(3, 6);
        } else {
            $nbPeople = mt_rand(2, 5);
        }

        $peopleGroup = (new PeopleGroup())
            ->setFamilyTypology($familyTypology)
            ->setNbPeople($nbPeople)
            ->setCreatedAt($createdAt)
            ->setCreatedBy($user)
            ->setUpdatedAt($updatedAt)
            ->setUpdatedBy($user);

        $this->objectManager->persist($peopleGroup);

        // Crée des fausses personnes dans le groupe
        $lastname = $this->faker->lastName();

        for ($i = 1; $i <= $nbPeople; ++$i) {
            $this->createPerson($peopleGroup, $lastname, $i);
        }
    }

    // Détermine différentes infos sur la personne en fonction de la typologie familiale
    private function familyTypology(int $familyTypology, int $l): void
    {
        if (1 === $familyTypology) {
            $this->setPerson('adult', Person::GENDER_FEMALE, true, 5);
        } elseif (2 === $familyTypology) {
            $this->setPerson('adult', Person::GENDER_MALE, true, 5);
        } elseif (3 === $familyTypology || 6 === $familyTypology) {
            if (1 === $l) {
                $this->setPerson('adult', Person::GENDER_FEMALE, true, 1);
            } elseif (2 === $l) {
                $this->setPerson('adult', Person::GENDER_MALE, false, 1);
            }
        } elseif (4 === $familyTypology) {
            if (1 === $l) {
                $this->setPerson('adult', Person::GENDER_FEMALE, true, 4);
            }
        } elseif (5 === $familyTypology) {
            if (1 === $l) {
                $this->setPerson('adult', Person::GENDER_MALE, true, 4);
            }
        }

        if (($familyTypology >= 4 && $familyTypology <= 5 && $l >= 2) || (6 === $familyTypology && $l >= 3)) {
            $this->setPerson('child', mt_rand(1, 2), false, RolePerson::ROLE_CHILD);
        }
    }

    private function setPerson(string $age, int $sex, bool $head, int $role): void
    {
        $this->firstname = $this->faker->firstName(Person::GENDER_FEMALE === $sex ? 'female' : 'male');
        $this->birthdate = $this->getBirthdate($age);
        $this->sex = $sex;
        $this->head = $head;
        $this->role = $role;
    }

    // Crée la personne
    private function createPerson(PeopleGroup $peopleGroup, string $lastname, int $i): Person
    {
        $this->familyTypology($peopleGroup->getFamilyTypology(), $i);

        $this->firstname = $this->faker->firstName();

        $phone = '06';
        for ($i = 1; $i < 5; ++$i) {
            $phone = $phone.' '.strval(mt_rand(0, 9)).strval(mt_rand(0, 9));
        }

        $person = (new Person())
            ->setFirstName($this->firstname)
            ->setLastName($lastname)
            ->setBirthdate($this->birthdate)
            ->setGender($this->sex)
            ->setEmail($this->faker->freeEmail())
            ->setPhone1($phone)
            ->setCreatedAt($peopleGroup->getCreatedAt())
            ->setUpdatedAt($peopleGroup->getUpdatedAt())
            ->setCreatedBy($peopleGroup->getCreatedBy())
            ->setUpdatedBy($peopleGroup->getCreatedBy());

        $this->objectManager->persist($person);

        $rolePerson = (new RolePerson())
            ->setHead($this->head)
            ->setRole($this->role)
            ->setPeopleGroup($peopleGroup)
            ->setPerson($person);

        $this->objectManager->persist($rolePerson);

        return $person;
    }

    // Donne une date de naissanc en fonction du role de la personne
    private function getBirthdate(string $role = 'adult'): DateTime
    {
        if ('adult' === $role) {
            return $this->faker->dateTimeBetween('-55 years', '-18 years');
        }

        return $this->faker->dateTimeBetween('-18 years', 'now');
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['people', 'support', 'evaluation', 'note', 'rdv', 'document', 'payment', 'tag'];
    }
}
