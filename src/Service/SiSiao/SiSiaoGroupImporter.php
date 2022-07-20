<?php

namespace App\Service\SiSiao;

use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Notification\ExceptionNotification;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\People\PersonRepository;
use App\Service\People\PeopleGroupChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class to import group and people from API SI-SIAO.
 */
class SiSiaoGroupImporter extends SiSiaoClient
{
    protected $em;
    protected $user;
    protected $personRepo;
    protected $peopleGroupRepo;
    protected $peopleGroupChecker;
    protected $flashBag;
    protected $translator;
    protected $exceptionNotification;

    public function __construct(
        HttpClientInterface $client,
        RequestStack $requestStack,
        EntityManagerInterface $em,
        Security $security,
        PersonRepository $personRepo,
        PeopleGroupRepository $peopleGroupRepo,
        PeopleGroupChecker $peopleGroupChecker,
        ExceptionNotification $exceptionNotification,
        TranslatorInterface $translator,
        string $url
    ) {
        parent::__construct($client, $requestStack, $url, $translator);

        $this->em = $em;
        $this->user = $security->getUser();
        $this->personRepo = $personRepo;
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->peopleGroupChecker = $peopleGroupChecker;
        $this->exceptionNotification = $exceptionNotification;

        /** @var Session */
        $session = $requestStack->getSession();
        $this->flashBag = $session->getFlashBag();
        $this->translator = $translator;
    }

    /**
     * Import a group by ID group.
     */
    public function import(int $id): ?PeopleGroup
    {
        try {
            return $this->createGroup($id);
        } catch (\Exception $e) {
            $this->exceptionNotification->sendException($e);

            $this->flashBag->add('danger', $this->translator->trans('sisiao.group.import_failed', [
                'error_message' => $this->getErrorMessage($e),
            ], 'app'));

            return null;
        }
    }

    /**
     * Create PeopleGroup and People.
     */
    protected function createGroup(int $id): ?PeopleGroup
    {
        /** @var object $result */
        $result = $this->searchById($id);

        if (!is_object($result) || 0 === $result->total) {
            $this->flashBag->add('warning', $this->translator->trans('sisiao.group_id_no_exist', [
                'sisiao_id' => $id,
            ], 'app'));

            return null;
        }

        /** @var object $ficheGroupe */
        $ficheGroupe = $this->get("/fiches/ficheSynthese/{$id}");

        $peopleGroup = $this->createPeopleGroup($ficheGroupe);

        foreach ($ficheGroupe->personnes as $personne) {
            $rolePerson = $this->createPerson($ficheGroupe, $personne, $peopleGroup);
            $peopleGroup->addRolePerson($rolePerson);
        }

        $this->peopleGroupChecker->checkValidHeader($peopleGroup);

        $this->em->flush();

        if ($peopleGroup->getCreatedAt() > (new \DateTime())->modify('-10 sec')) {
            $this->flashBag->add('success', 'sisiao.group.imported_successfully');
        }

        return $peopleGroup;
    }

    protected function createPeopleGroup(object $ficheGroupe): PeopleGroup
    {
        if ($peopleGroup = $this->peopleGroupExists($ficheGroupe)) {
            $this->flashBag->add('warning', 'sisiao.group.already_exists');
        } else {
            $peopleGroup = (new PeopleGroup())
            ->setFamilyTypology($this->findInArray($ficheGroupe->composition, SiSiaoItems::FAMILY_TYPOLOGY) ?? 9)
            ->setNbPeople(count($ficheGroupe->personnes))
            ->setSiSiaoId($ficheGroupe->id)
            ->setSiSiaoImport(true)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

            $this->em->persist($peopleGroup);
        }

        return $peopleGroup;
    }

    protected function createPerson(object $ficheGroupe, object $personne, PeopleGroup $peopleGroup): RolePerson
    {
        if ($person = $this->personExists($personne)) {
            $this->flashBag->add('warning', $this->translator->trans('sisiao.person.already_exists', [
                'person_firstname' => $person->getFirstname(),
            ], 'app'));
        } else {
            $person = (new Person())
            ->setLastname($personne->nom)
            ->setFirstname($personne->prenom)
            ->setBirthdate($this->convertDate($personne->datenaissance))
            ->setGender($this->findInArray($personne->sexe, SiSiaoItems::GENDERS))
            ->setMaidenName($personne->nomJeuneFille ?? $personne->nomUsage)
            ->setSiSiaoId($this->getFichePersonneId($personne))
            ->setPhone1(preg_match('^0[1-9]([-._/ ]?[0-9]{2}){4}$^', $personne->telephone) ? $personne->telephone : null)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

            if (null !== $ficheGroupe->contactPrincipal
                && $ficheGroupe->contactPrincipal->id === $personne->id
                && $ficheGroupe->courrielDemandeur
                && preg_match('^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$^', $ficheGroupe->courrielDemandeur)
            ) {
                $person->setEmail($ficheGroupe->courrielDemandeur);
            }

            $this->em->persist($person);
        }

        return $this->createRolePerson($person, $peopleGroup, $ficheGroupe, $personne);
    }

    protected function createRolePerson(Person $person, PeopleGroup $peopleGroup, object $ficheGroupe, object $personne): RolePerson
    {
        if ($rolePerson = $this->rolePersonExists($peopleGroup, $person)) {
            return $rolePerson;
        }

        $rolePerson = (new RolePerson())
            ->setHead((null !== $ficheGroupe->contactPrincipal && $ficheGroupe->contactPrincipal->id === $personne->id)
                || 1 === count($ficheGroupe->personnes))
            ->setRole($this->getRole($ficheGroupe, $personne))
            ->setPerson($person)
            ->setPeopleGroup($peopleGroup);

        $this->em->persist($rolePerson);

        return $rolePerson;
    }

    protected function peopleGroupExists(object $ficheGroupe): ?PeopleGroup
    {
        return $this->peopleGroupRepo->findOneBy([
            'siSiaoId' => $ficheGroupe->id,
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

    protected function getRole(object $ficheGroupe, object $personne): int
    {
        if ($personne->age < 18) {
            return RolePerson::ROLE_CHILD; // Enfant
        }

        if ($this->findInArray($ficheGroupe->composition, [10, 20])) {
            return 5; // Personne isolée
        }
        if ($this->findInArray($ficheGroupe->composition, [40, 50])) {
            return 4; // Parent isolé
        }

        return $this->findInArray($personne->situation->id, SiSiaoItems::ROLE) ?? 99;
    }
}
