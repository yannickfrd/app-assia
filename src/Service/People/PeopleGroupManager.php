<?php

namespace App\Service\People;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use App\Repository\People\RolePersonRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Grammar;
use App\Service\SupportGroup\SupportManager;
use App\Service\SupportGroup\SupportPeopleAdder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;

class PeopleGroupManager
{
    /** @var User */
    private $user;
    private $em;
    private $peopleGroupChecker;
    private $requestStack;
    private $flashBag;

    public function __construct(
        Security $security,
        EntityManagerInterface $em,
        PeopleGroupChecker $peopleGroupChecker,
        RequestStack $requestStack
    ) {
        $this->user = $security->getUser();
        $this->em = $em;
        $this->peopleGroupChecker = $peopleGroupChecker;
        $this->requestStack = $requestStack;

        /** @var Session */
        $session = $requestStack->getSession();
        $this->flashBag = $session->getFlashBag();
    }

    public function update(PeopleGroup $peopleGroup, array $supports = [])
    {
        $this->peopleGroupChecker->checkValidHeader($peopleGroup);
        $this->updateNbPeople($peopleGroup);

        $this->em->flush();

        $this->deleteCacheItems($peopleGroup, $supports);
    }

    /**
     * Create a new person in the group.
     */
    public function createPersonInGroup(PeopleGroup $peopleGroup, Person $person, RolePerson $rolePerson, bool $addPersonToSupport = false): Person
    {
        $this->addRolePerson($peopleGroup, $person, $rolePerson, $addPersonToSupport);

        $this->em->persist($person);

        $this->em->flush();

        return $person;
    }

    /**
     * Add person in the group.
     */
    public function addPerson(PeopleGroup $peopleGroup, Person $person, RolePerson $rolePerson, bool $addPersonToSupport = false): ?Person
    {
        if ($this->personIsInGroup($peopleGroup, $person)) {
            $this->flashBag->add('warning', $person->getFullname().' est déjà dans le groupe.');

            return null;
        }

        $this->addRolePerson($peopleGroup, $person, $rolePerson, $addPersonToSupport);

        return $person;
    }

    /**
     * Remove the person from a group.
     */
    public function removePerson(RolePerson $rolePerson): void
    {
        $person = $rolePerson->getPerson();
        $peopleGroup = $rolePerson->getPeopleGroup();
        $nbPeople = count($peopleGroup->getRolePeople());

        if ($rolePerson->getHead()) {
            $this->flashBag->add('danger', 'Le demandeur principal ne peut pas être retiré du groupe.');
        }

        $peopleGroup->removeRolePerson($rolePerson);
        $peopleGroup->setNbPeople($nbPeople - 1);

        $this->em->flush();

        $this->flashBag->add('warning', $person->getFullname().' est retiré'.Grammar::gender($person->getGender()).' du groupe.');
    }

    /**
     * @param SupportGroup[] $supports
     */
    public static function deleteCacheItems(PeopleGroup $peopleGroup, array $supports = []): void
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        $cache->deleteItems([
            PeopleGroup::CACHE_GROUP_REFERENTS_KEY.$peopleGroup->getId(),
            PeopleGroup::CACHE_GROUP_SUPPORTS_KEY.$peopleGroup->getId(),
        ]);

        foreach ($supports as $supportGroup) {
            $cache->deleteItems([
                SupportGroup::CACHE_SUPPORT_KEY.$supportGroup->getId(),
                SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId(),
                EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId(),
            ]);
        }
    }

    private function updateNbPeople(PeopleGroup $peopleGroup)
    {
        $nbPeople = count($peopleGroup->getRolePeople());
        $peopleGroup->setNbPeople($nbPeople);
    }

    /**
     * Add role of the person in the group and add person in the active support(s).
     */
    private function addRolePerson(PeopleGroup $peopleGroup, Person $person, RolePerson $rolePerson, bool $addPersonToSupport = false): void
    {
        $rolePerson
            ->setHead(false)
            ->setPerson($person)
            ->setPeopleGroup($peopleGroup)
        ;

        $this->em->persist($rolePerson);

        $peopleGroup->addRolePerson($rolePerson);

        $this->update($peopleGroup);

        $this->flashBag->add('success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au groupe.');

        $this->addPersonToActiveSupport($peopleGroup, $rolePerson, $addPersonToSupport);
    }

    /**
     * Add person in the active support.
     */
    private function addPersonToActiveSupport(PeopleGroup $peopleGroup, RolePerson $rolePerson, bool $addPersonToSupport = false): void
    {
        if (false === $addPersonToSupport) {
            return;
        }

        /** @var SupportGroupRepository $supportGroupRepo */
        $supportGroupRepo = $this->em->getRepository(SupportGroup::class);

        foreach ($supportGroupRepo->findBy(['peopleGroup' => $peopleGroup]) as $supportGroup) {
            if (SupportGroup::STATUS_IN_PROGRESS === $supportGroup->getStatus()
                && ($this->user->hasService($supportGroup->getService()))) {
                (new SupportPeopleAdder($this->em, $this->requestStack))->addPersonToSupport($supportGroup, $rolePerson);

                SupportManager::deleteCacheItems($supportGroup);
            }
        }
    }

    /**
     * Check if the person is already in the group.
     */
    private function personIsInGroup(PeopleGroup $peopleGroup, Person $person): bool
    {
        /** @var RolePersonRepository $rolePersonRepo */
        $rolePersonRepo = $this->em->getRepository(RolePerson::class);

        return 0 !== $rolePersonRepo->count([
            'person' => $person->getId(),
            'peopleGroup' => $peopleGroup->getId(),
        ]);
    }
}
