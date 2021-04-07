<?php

namespace App\DataFixtures;

use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Repository\Organization\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/*
 * @codeCoverageIgnore
 */
class C_PeopleGroupFixtures extends Fixture
{
    private $manager;

    private $user;

    private $peopleGroup;
    private $familyTypology;
    private $nbPeople;
    private $groupCreatedAt;
    private $groupUpdatedAt;
    private $rolePerson;
    private $head;
    private $role;
    private $person;
    private $lastname;
    private $firstname;
    private $birthdate;
    private $sex;

    public function __construct(EntityManagerInterface $manager, UserRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $this->init(); // Fixtures
    }

    protected function init()
    {
        $users = $this->repo->findAll();

        foreach ($users as $user) {
            $this->user = $user;

            // Crée des faux groupes
            for ($i = 1; $i <= mt_rand(10, 15); ++$i) {
                $this->setTypology();
                $this->addPeopleGroup();

                // Crée des fausses personnes dans le groupe
                for ($j = 1; $j <= $this->nbPeople; ++$j) {
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
        } elseif (3 === $this->familyTypology) {
            $this->nbPeople = 2;
        } elseif (6 === $this->familyTypology) {
            $this->nbPeople = mt_rand(3, 6);
        } else {
            $this->nbPeople = mt_rand(2, 5);
        }
    }

    // Crée le groupe
    public function addPeopleGroup()
    {
        // Définit la date de création et de mise à jour
        $this->groupCreatedAt = AppFixtures::getDateTimeBeetwen('-24 months', 'now');
        $this->groupUpdatedAt = AppFixtures::getDateTimeBeetwen(AppFixtures::getStartDate($this->groupCreatedAt), 'now');

        $this->lastname = $this->faker->lastName();

        $this->peopleGroup = (new PeopleGroup())
            ->setFamilyTypology($this->familyTypology)
            ->setNbPeople($this->nbPeople)
            ->setCreatedAt($this->groupCreatedAt)
            ->setCreatedBy($this->user)
            ->setUpdatedAt($this->groupUpdatedAt)
            ->setUpdatedBy($this->user);

        $this->manager->persist($this->peopleGroup);
    }

    // Détermine différentes infos sur la personne en fonction de la typologie familiale
    protected function familyTypology($l)
    {
        if (1 === $this->familyTypology) {
            $this->setPerson('adult', 1, true, 5);
        } elseif (2 === $this->familyTypology) {
            $this->setPerson('adult', 2, true, 5);
        } elseif (3 === $this->familyTypology || 6 === $this->familyTypology) {
            if (1 === $l) {
                $this->setPerson('adult', 1, true, 1);
            } elseif (2 === $l) {
                $this->setPerson('adult', 2, false, 1);
            }
        } elseif (4 === $this->familyTypology) {
            if (1 === $l) {
                $this->setPerson('adult', 1, true, 4);
            }
        } elseif (5 === $this->familyTypology) {
            if (1 === $l) {
                $this->setPerson('adult', 2, true, 4);
            }
        }

        if (($this->familyTypology >= 4 && $this->familyTypology <= 5 && $l >= 2) || (6 === $this->familyTypology && $l >= 3)) {
            $this->setPerson('child', mt_rand(1, 2), false, 3);
        }
    }

    protected function setPerson($age, $sex, $head, $role)
    {
        $this->firstname = $this->faker->firstName(1 === $sex ? 'female' : 'male');
        $this->birthdate = $this->birthdate($age);
        $this->sex = $sex;
        $this->head = $head;
        $this->role = $role;
    }

    // Crée le rôle de la personne dans le groupe
    public function addRolePerson()
    {
        $this->rolePerson = (new RolePerson())
            ->setHead($this->head)
            ->setRole($this->role)
            ->setPeopleGroup($this->peopleGroup)
            ->setCreatedAt($this->groupCreatedAt);

        $this->manager->persist($this->rolePerson);
    }

    // Crée la personne
    public function addPerson()
    {
        $this->firstname = $this->faker->firstName();

        $phone = '06';
        for ($i = 1; $i < 5; ++$i) {
            $phone = $phone.' '.strval(mt_rand(0, 9)).strval(mt_rand(0, 9));
        }

        $this->person = (new Person())
            ->setFirstName($this->firstname)
            ->setLastName($this->lastname)
            ->setBirthdate($this->birthdate)
            ->setGender($this->sex)
            ->setEmail($this->faker->freeEmail())
            ->setPhone1($phone)
            ->setCreatedAt($this->groupCreatedAt)
            ->setUpdatedAt($this->groupUpdatedAt)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user)
            ->addRolesPerson($this->rolePerson);
        // Prépare le manager à faire persister les données dans le temps
        $this->manager->persist($this->person);
    }

    // Donne une date de naissanc en fonction du role de la personne
    protected function birthdate($role = 'adult')
    {
        if ('adult' === $role) {
            $birthdate = $this->faker->dateTimeBetween($startDate = '-55 years', $endDate = '-18 years', $timezone = null);
        } else {
            $birthdate = $this->faker->dateTimeBetween($startDate = '-18 years', $endDate = 'now', $timezone = null);
        }

        return $birthdate;
    }
}
