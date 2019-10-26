<?php

namespace App\DataFixtures;

use App\Entity\Pole;
use App\Entity\User;
use App\Entity\Person;
use App\Entity\RoleUser;
use App\Entity\Department;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Entity\SocialSupportGrp;
use App\Entity\SocialSupportPers;
use App\Repository\RolePersonRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $manager;

    public const DEPARTMENTS = [
        1 => "ALTHO",
        2 => "ASSLT - ASLLT",
        3 => "10 000 logements accompagnés"
    ];

    private $pole;
    private $department;
    private $roleUser;
    private $user, $passwordEncoder;
    private $groupPeople, $familyTypology, $nbPeople, $groupCreatedAt, $groupUpdatedAt;
    private $rolePerson, $head, $role;
    private $person, $lastname, $firstname, $birthdate, $sex;
    private $socialSupportGrp, $nbSocialSupports, $startDate, $endDate, $status;

    public function __construct(ObjectManager $manager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->manager = $manager;
        $this->faker = \Faker\Factory::create("fr_FR");
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        //Crée les pôles d'activité
        foreach (Pole::POLES as $key => $value) {
            $this->addPoles($value);
            if ($key == 3) {
                //Créee les services d'activité
                foreach ($this::DEPARTMENTS as $key => $value) {
                    $this->addDepartment($value);
                    // Crée des faux utilisateurs
                    for ($i = 1; $i <= mt_rand(3, 6); $i++) {
                        $this->addRoleUser();
                        $this->addUser();
                        // Crée des faux groupes
                        for ($j = 1; $j <= mt_rand(15, 20); $j++) {
                            $this->setTypology();
                            $this->addGroupPeople();
                            //Crée des faux suivis sociaux 
                            $this->nbSocialSupports = mt_rand(1, 2);
                            for ($k = 1; $k <= $this->nbSocialSupports; $k++) {
                                $this->addSocialSupportGrp($k);
                            }
                            // Crée des fausses personnes pour le groupe
                            for ($l = 1; $l <= $this->nbPeople; $l++) {
                                $this->familyTypology($l);
                                $this->addRolePerson();
                                $this->addPerson();
                                $this->addSocialSupportPers();
                            }
                            $this->manager->flush();
                        }
                    }
                }
            }
        }
    }

    public function addPoles($value)
    {
        $this->pole = new Pole();

        $this->pole->setName($value)
            ->setCreatedAt(new \DateTime());

        $this->manager->persist($this->pole);
    }

    public function addDepartment($value)
    {
        $this->department = new Department();

        $this->department->setName($value)
            ->setPole($this->pole)
            ->setCreatedAt(new \DateTime());

        $this->manager->persist($this->department);
    }

    public function addRoleUser()
    {
        $this->roleUser = new RoleUser();

        $this->roleUser->setRole(1)
            ->setDepartment($this->department);

        $this->manager->persist($this->roleUser);
    }


    public function addUser()
    {
        $this->user = new User();
        // Définit la date de création et de mise à jour
        $createdAt = $this->getDateTimeBeetwen("-2 years", "-12 month");
        $lastLogin = $this->getDateTimeBeetwen("-2 months", "now");

        $firstname = $this->faker->firstName();

        $this->user->setUsername($firstname)
            ->setFirstName($firstname)
            ->setLastName($this->faker->lastName())
            ->setPassword($this->passwordEncoder->encodePassword($this->user, "test123"))
            ->setEmail($this->faker->freeEmail())
            ->setCreatedAt($createdAt)
            ->setLoginCount(mt_rand(0, 99))
            ->setLastLogin($lastLogin)
            ->addRoleUser($this->roleUser);


        $this->manager->persist($this->user);
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

    // Crée le groupe groupe
    public function addGroupPeople()
    {
        // Définit la date de création et de mise à jour
        $this->groupCreatedAt = $this->getDateTimeBeetwen("-24 months", "now");
        $this->groupUpdatedAt = $this->getDateTimeBeetwen($this->getStartDate($this->groupCreatedAt), "now");

        $this->lastname = $this->faker->lastName();

        $this->groupPeople = new GroupPeople();
        $this->groupPeople->setFamilyTypology($this->familyTypology)
            ->setNbPeople($this->nbPeople)
            ->setComment($this->faker->paragraph())
            ->setCreatedAt($this->groupCreatedAt)
            ->setCreatedBy($this->user)
            ->setUpdatedAt($this->groupUpdatedAt)
            ->setUpdatedBy($this->user);

        $this->manager->persist($this->groupPeople);
    }

    protected function getStartDate($date)
    {
        $now = new \DateTime();
        $interval = $now->diff($date);
        $days = $interval->days;
        return "-" . $days . " days";
    }

    // Crée le suivi social du groupe
    public function addSocialSupportGrp($k)
    {
        $this->socialSupportGrp = new SocialSupportGrp();

        $comment = "<p>" . join($this->faker->paragraphs(3), "</p><p>") . "</p>";

        if ($this->nbSocialSupports >= 2 && $k == 1) {
            $this->status = 4;
            $this->startDate = $this->getDateTimeBeetwen($this->groupCreatedAt, "now");
            $this->endDate = $this->getDateTimeBeetwen($this->getStartDate($this->startDate, "now"));
        } else if ($this->nbSocialSupports >= 2 && $k == 2) {
            $this->status = 2;
            $this->startDate = $this->getDateTimeBeetwen($this->getStartDate($this->endDate, "now"));
            $this->endDate = null;
        } else {
            $this->status = mt_rand(2, 4);
            $this->startDate = $this->getDateTimeBeetwen($this->groupCreatedAt, "now");
            if ($this->status == 4) {
                $this->endDate = $this->getDateTimeBeetwen($this->getStartDate($this->startDate, "now"));
            } else {
                $this->endDate = null;
            }
        }

        $this->socialSupportGrp->setStartDate($this->startDate)
            ->setEndDate($this->endDate ?? null)
            ->setStatus($this->status)
            ->setComment($comment)
            ->setCreatedAt($this->startDate)
            ->setUpdatedAt($this->groupUpdatedAt)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user)
            ->setGroupPeople($this->groupPeople)
            ->setDepartment($this->department);

        $this->manager->persist($this->socialSupportGrp);
    }
    // Crée le suivi social du groupe
    public function addSocialSupportPers()
    {
        $socialSupportPers = new SocialSupportPers();

        $comment = "<p>" . join($this->faker->paragraphs(3), "</p><p>") . "</p>";

        $socialSupportPers->setStartDate($this->startDate)
            ->setEndDate($this->endDate ?? null)
            ->setStatus($this->status)
            ->setComment($comment)
            ->setCreatedAt($this->startDate)
            ->setUpdatedAt($this->groupUpdatedAt)
            // ->setCreatedBy($this->user)
            // ->setUpdatedBy($this->user)
            ->setPerson($this->person)
            ->setSocialSupportGrp($this->socialSupportGrp);

        $this->manager->persist($socialSupportPers);
    }

    protected function getDateTimeBeetwen($startEnd, $endDate = "now")
    {
        return $this->faker->dateTimeBetween($startEnd, $endDate, $timezone = null);
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
            // ->setCreatedBy($this->user)
            ->setCreatedAt($this->groupCreatedAt);

        $this->manager->persist($this->rolePerson);
    }

    // Crée la personne
    public function addPerson()
    {
        $this->person = new Person();

        $this->person->setFirstName($this->firstname)
            ->setLastName($this->lastname)
            ->setBirthdate($this->birthdate)
            ->setGender($this->sex)
            ->setEmail($this->faker->freeEmail())
            ->setphone1($this->faker->mobileNumber())
            ->setComment($this->faker->paragraph())
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
