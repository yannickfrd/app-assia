<?php

namespace App\Service\SiSiao;

use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\People\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class to import group and people from API SI-SIAO.
 */
class SiSiaoGroupImporter extends SiSiaoRequest
{
    use SiSiaoClientTrait;

    protected $manager;
    protected $user;
    protected $personRepo;
    protected $peopleGroupRepo;
    protected $flashBag;

    /** @var object */
    protected $ficheGroupe;

    public function __construct(
        HttpClientInterface $client,
        SessionInterface $session,
        EntityManagerInterface $manager,
        Security $security,
        PersonRepository $personRepo,
        PeopleGroupRepository $peopleGroupRepo,
        FlashBagInterface $flashBag,
        string $url
    ) {
        parent::__construct($client, $session, $url);

        $this->manager = $manager;
        $this->user = $security->getUser();
        $this->personRepo = $personRepo;
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->flashBag = $flashBag;
    }

    /**
     * Import a group by ID group.
     */
    public function import(int $id): ?PeopleGroup
    {
        try {
            return $this->createGroup($id);
        } catch (\Exception $e) {
            $this->flashBag->add('danger', "Le groupe n'a pas pu être importé. ".$this->getErrorMessage($e));

            return null;
        }
    }

    /**
     * Create PeopleGroup and People.
     */
    protected function createGroup(int $id): ?PeopleGroup
    {
        $result = $this->searchById($id);

        if (0 === $result->total) {
            $this->flashBag->add('warning', "Il n'y a pas de dossier SI-SIAO correspondant avec la clé '$id'.");

            return null;
        }

        $this->ficheGroupe = $this->get("fiches/ficheSynthese/{$id}");

        $peopleGroup = $this->createPeopleGroup($this->ficheGroupe);

        foreach ($this->ficheGroupe->personnes as $personne) {
            $rolePerson = $this->createPerson($personne, $peopleGroup);
            $peopleGroup->addRolePerson($rolePerson);
        }

        $this->manager->flush();

        return $peopleGroup;
    }

    protected function createPeopleGroup(): PeopleGroup
    {
        if ($peopleGroup = $this->peopleGroupExists($this->ficheGroupe)) {
            $this->flashBag->add('warning', 'Ce groupe existe déjà.');
        } else {
            $peopleGroup = (new PeopleGroup())
            ->setFamilyTypology($this->findInArray($this->ficheGroupe->composition, SiSiaoItems::FAMILY_TYPOLOGY))
            ->setNbPeople(count($this->ficheGroupe->personnes))
            ->setSiSiaoId($this->ficheGroupe->id)
            ->setSiSiaoImport(true)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

            $this->manager->persist($peopleGroup);

            $this->flashBag->add('success', 'Le groupe a été importé.');
        }

        return $peopleGroup;
    }

    protected function createPerson(object $personne, PeopleGroup $peopleGroup): RolePerson
    {
        if ($person = $this->personExists($personne)) {
            $this->flashBag->add('warning', $person->getFullname().' existe déjà.');
        } else {
            $person = (new Person())
            ->setLastname($personne->nom)
            ->setFirstname($personne->prenom)
            ->setBirthdate($this->convertDate($personne->datenaissance))
            ->setGender($this->findInArray($personne->sexe, SiSiaoItems::GENDERS))
            ->setMaidenName($personne->nomJeuneFille ?? $personne->nomUsage)
            ->setSiSiaoId($this->getFichePersonneId($personne))
            ->setPhone1($personne->telephone)
            ->setEmail($personne->id === $this->ficheGroupe->demandeurprincipal->id ?
                $this->ficheGroupe->courrielDemandeur : null)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

            $this->manager->persist($person);
        }

        return $this->createRolePerson($person, $peopleGroup, $personne);
    }

    protected function createRolePerson(Person $person, PeopleGroup $peopleGroup, object $personne): RolePerson
    {
        if ($rolePerson = $this->rolePersonExists($peopleGroup, $person)) {
            return $rolePerson;
        }

        $rolePerson = (new RolePerson())
            ->setHead($personne->id === $this->ficheGroupe->demandeurprincipal->id)
            ->setRole($this->getRole($personne))
            ->setPerson($person)
            ->setPeopleGroup($peopleGroup);

        $this->manager->persist($rolePerson);

        return $rolePerson;
    }

    protected function peopleGroupExists(): ?PeopleGroup
    {
        return $this->peopleGroupRepo->findOneBy([
            'siSiaoId' => $this->ficheGroupe->id,
        ]);
    }

    protected function personExists(object $personne): ?Person
    {
        return $this->personRepo->findOneBy([
            'firstname' => $personne->prenom,
            'lastname' => $personne->nom,
            'birthdate' => $this->convertDate($personne->datenaissance),
        ]);
    }

    protected function rolePersonExists(PeopleGroup $peopleGroup, Person $person): ?RolePerson
    {
        foreach ($peopleGroup->getRolePeople() as $rolePerson) {
            if ($person->getId() && $person->getId() === $rolePerson->getPerson()->getId()) {
                return $rolePerson;
            }
        }

        return null;
    }

    protected function getRole(object $personne): int
    {
        if ($personne->age < 18) {
            return RolePerson::ROLE_CHILD; // Enfant
        }
        if (in_array($this->ficheGroupe->composition->id, [10, 20])) {
            return 5; // Personne isolée
        }
        if (in_array($this->ficheGroupe->composition->id, [40, 50])) {
            return 4; // Parent isolé
        }

        return $this->findInArray($personne->situation->id, SiSiaoItems::ROLE);
    }
}