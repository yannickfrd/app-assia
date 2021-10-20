<?php

namespace App\Service\People;

use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use App\Event\People\PeopleGroupEvent;
use App\Event\Support\SupportGroupEvent;
use App\Repository\People\RolePersonRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Security\CurrentUserService;
use App\Service\Grammar;
use App\Service\SupportGroup\SupportPeopleAdder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PeopleGroupManager
{
    private $manager;
    private $currentUserService;
    private $dispatcher;
    private $flashbag;

    public function __construct(
        EntityManagerInterface $manager,
        CurrentUserService $currentUserService,
        EventDispatcherInterface $dispatcher,
        FlashBagInterface $flashbag
    ) {
        $this->manager = $manager;
        $this->currentUserService = $currentUserService;
        $this->dispatcher = $dispatcher;
        $this->flashbag = $flashbag;
    }

    /**
     * Create a new person in the group.
     */
    public function createPersonInGroup(PeopleGroup $peopleGroup, Person $person, RolePerson $rolePerson, bool $addPersonToSupport = false): Person
    {
        $this->addRolePerson($peopleGroup, $person, $rolePerson, $addPersonToSupport);

        $this->manager->persist($person);

        $this->manager->flush();

        return $person;
    }

    /**
     * Add person in the group.
     */
    public function addPerson(PeopleGroup $peopleGroup, Person $person, RolePerson $rolePerson, bool $addPersonToSupport = false): ?Person
    {
        if ($this->personIsInGroup($peopleGroup, $person)) {
            $this->flashbag->add('warning', $person->getFullname().' est déjà dans le groupe.');

            return null;
        }

        $this->addRolePerson($peopleGroup, $person, $rolePerson, $addPersonToSupport);

        $this->manager->flush();

        return $person;
    }

    /**
     * Add role of the person in the group and add person in the active support(s).
     */
    protected function addRolePerson(PeopleGroup $peopleGroup, Person $person, RolePerson $rolePerson, bool $addPersonToSupport = false): void
    {
        $rolePerson->setHead(false)
            ->setPerson($person)
            ->setPeopleGroup($peopleGroup);

        $this->manager->persist($rolePerson);

        $peopleGroup->addRolePerson($rolePerson);

        $this->dispatcher->dispatch(new PeopleGroupEvent($peopleGroup), 'people_group.before_update');

        $this->flashbag->add('success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au groupe.');

        $this->addPersonToActiveSupport($peopleGroup, $rolePerson, $addPersonToSupport);
    }

    /**
     * Add person in the active support.
     */
    protected function addPersonToActiveSupport(PeopleGroup $peopleGroup, RolePerson $rolePerson, bool $addPersonToSupport = false): void
    {
        if (false === $addPersonToSupport) {
            return;
        }

        /** @var SupportGroupRepository $supportGroupRepo */
        $supportGroupRepo = $this->manager->getRepository(SupportGroup::class);

        foreach ($supportGroupRepo->findBy(['peopleGroup' => $peopleGroup]) as $supportGroup) {
            if (SupportGroup::STATUS_IN_PROGRESS === $supportGroup->getStatus()
                && ($this->currentUserService->isInService($supportGroup->getService()))) {
                (new SupportPeopleAdder($this->manager, $this->flashbag))->addPersonToSupport($supportGroup, $rolePerson);

                $this->dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.after_update');
            }
        }
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
            $this->flashbag->add('danger', 'Le demandeur principal ne peut pas être retiré du groupe.');
        }

        $peopleGroup->removeRolePerson($rolePerson);
        $peopleGroup->setNbPeople($nbPeople - 1);

        $this->manager->flush();

        $this->flashbag->add('warning', $person->getFullname().' est retiré'.Grammar::gender($person->getGender()).' du groupe.');
    }

    /**
     * Check if the person is already in the group.
     */
    protected function personIsInGroup(PeopleGroup $peopleGroup, Person $person): bool
    {
        /** @var RolePersonRepository $rolePersonRepo */
        $rolePersonRepo = $this->manager->getRepository(RolePerson::class);

        return 0 != $rolePersonRepo->count([
            'person' => $person->getId(),
            'peopleGroup' => $peopleGroup->getId(),
        ]);
    }
}
