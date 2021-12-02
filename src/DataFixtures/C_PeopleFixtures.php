<?php

namespace App\DataFixtures;

use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Repository\Organization\UserRepository;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class C_PeopleFixtures extends Fixture
{
    private $em;
    private $userRepository;
    private $faker;

    private $user;

    private $familyTypology;
    private $nbPeople;
    private $createdAt;
    private $updatedAt;
    private $head;
    private $role;
    private $lastname;
    private $firstname;
    private $birthdate;
    private $sex;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $em): void
    {
        foreach ($this->userRepository->findAll() as $user) {
            $this->user = $user;
            // Crée des faux groupes
            for ($i = 1; $i <= mt_rand(10, 15); ++$i) {
                $this->setTypology();
                $peopleGroup = $this->createPeopleGroup();

                // Crée des fausses personnes dans le groupe
                for ($j = 1; $j <= $this->nbPeople; ++$j) {
                    $this->familyTypology($j);
                    $this->createPerson($peopleGroup);
                }
            }
        }
        $this->em->flush();
    }

    // Définit la typologie familiale et le nombre de personnes
    protected function setTypology(): void
    {
        // Définit la typologie familiale
        $this->familyTypology = mt_rand(1, 6);
        if ($this->familyTypology <= 2) {
            $this->nbPeople = 1;
        } elseif (3 === $this->familyTypology) {
            $this->nbPeople = 2;
        } elseif (6 === $this->familyTypology) {
            $this->nbPeople = mt_rand(3, 6);
        } else {
            $this->nbPeople = mt_rand(2, 5);
        }
    }

    // Crée le groupe
    public function createPeopleGroup(): PeopleGroup
    {
        // Définit la date de création et de mise à jour
        $this->createdAt = AppFixtures::getDateTimeBeetwen('-24 months', 'now');
        $this->updatedAt = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->createdAt), 'now');

        $this->lastname = $this->faker->lastName();

        $peopleGroup = (new PeopleGroup())
            ->setFamilyTypology($this->familyTypology)
            ->setNbPeople($this->nbPeople)
            ->setCreatedAt($this->createdAt)
            ->setCreatedBy($this->user)
            ->setUpdatedAt($this->updatedAt)
            ->setUpdatedBy($this->user);

        $this->em->persist($peopleGroup);

        return $peopleGroup;
    }

    // Détermine différentes infos sur la personne en fonction de la typologie familiale
    protected function familyTypology(int $l): void
    {
        if (1 === $this->familyTypology) {
            $this->setPerson('adult', Person::GENDER_FEMALE, true, 5);
        } elseif (2 === $this->familyTypology) {
            $this->setPerson('adult', Person::GENDER_MALE, true, 5);
        } elseif (3 === $this->familyTypology || 6 === $this->familyTypology) {
            if (1 === $l) {
                $this->setPerson('adult', Person::GENDER_FEMALE, true, 1);
            } elseif (2 === $l) {
                $this->setPerson('adult', Person::GENDER_MALE, false, 1);
            }
        } elseif (4 === $this->familyTypology) {
            if (1 === $l) {
                $this->setPerson('adult', Person::GENDER_FEMALE, true, 4);
            }
        } elseif (5 === $this->familyTypology) {
            if (1 === $l) {
                $this->setPerson('adult', Person::GENDER_MALE, true, 4);
            }
        }

        if (($this->familyTypology >= 4 && $this->familyTypology <= 5 && $l >= 2) || (6 === $this->familyTypology && $l >= 3)) {
            $this->setPerson('child', mt_rand(1, 2), false, RolePerson::ROLE_CHILD);
        }
    }

    protected function setPerson(string $age, int $sex, bool $head, int $role): void
    {
        $this->firstname = $this->faker->firstName(Person::GENDER_FEMALE === $sex ? 'female' : 'male');
        $this->birthdate = $this->getBirthdate($age);
        $this->sex = $sex;
        $this->head = $head;
        $this->role = $role;
    }

    // Crée la personne
    public function createPerson(PeopleGroup $peopleGroup): Person
    {
        $this->firstname = $this->faker->firstName();

        $phone = '06';
        for ($i = 1; $i < 5; ++$i) {
            $phone = $phone.' '.strval(mt_rand(0, 9)).strval(mt_rand(0, 9));
        }

        $person = (new Person())
            ->setFirstName($this->firstname)
            ->setLastName($this->lastname)
            ->setBirthdate($this->birthdate)
            ->setGender($this->sex)
            ->setEmail($this->faker->freeEmail())
            ->setPhone1($phone)
            ->setCreatedAt($this->createdAt)
            ->setUpdatedAt($this->updatedAt)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        $this->em->persist($person);

        $rolePerson = (new RolePerson())
            ->setHead($this->head)
            ->setRole($this->role)
            ->setPeopleGroup($peopleGroup)
            ->setPerson($person)
            ->setCreatedAt($this->createdAt);

        $this->em->persist($rolePerson);

        return $person;
    }

    // Donne une date de naissanc en fonction du role de la personne
    protected function getBirthdate(string $role = 'adult'): DateTime
    {
        if ('adult' === $role) {
            return $this->faker->dateTimeBetween('-55 years', '-18 years');
        }

        return $this->faker->dateTimeBetween('-18 years', 'now');
    }
}
