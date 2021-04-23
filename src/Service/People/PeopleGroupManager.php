<?php

namespace App\Service\People;

use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Event\People\PeopleGroupEvent;
use App\Repository\People\RolePersonRepository;
use App\Service\Grammar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PeopleGroupManager
{
    private $manager;
    private $rolePersonRepo;
    private $dispatcher;
    private $flashbag;

    public function __construct(
        EntityManagerInterface $manager,
        RolePersonRepository $rolePersonRepo,
        EventDispatcherInterface $dispatcher,
        FlashBagInterface $flashbag
    ) {
        $this->manager = $manager;
        $this->rolePersonRepo = $rolePersonRepo;
        $this->dispatcher = $dispatcher;
        $this->flashbag = $flashbag;
    }

    /**
     * Ajoute une personne dans le groupe.
     */
    public function addPerson(PeopleGroup $peopleGroup, Person $person, RolePerson $rolePerson): ?RolePerson
    {
        // Si la personne est asssociée, ne fait rien, créé la liaison
        if ($this->personExists($peopleGroup, $person)) {
            $this->flashbag->add('warning', $person->getFullname().' est déjà dans le groupe.');

            return null;
        }

        $rolePerson->setHead(false)
            ->setPerson($person)
            ->setPeopleGroup($peopleGroup);

        $this->manager->persist($rolePerson);

        $peopleGroup->addRolePerson($rolePerson);

        $this->dispatcher->dispatch(new PeopleGroupEvent($peopleGroup), 'people_group.before_update');

        $this->manager->flush();

        $this->flashbag->add('success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au groupe.');

        return $rolePerson;
    }

    /**
     * Retire une personne d'un groupe.
     */
    public function removePerson(RolePerson $rolePerson): array
    {
        $person = $rolePerson->getPerson();
        $peopleGroup = $rolePerson->getPeopleGroup();
        $nbPeople = count($peopleGroup->getRolePeople());

        if ($rolePerson->getHead()) {
            return [
                'alert' => 'danger',
                'msg' => 'Le demandeur principal ne peut pas être retiré du groupe.',
            ];
        }

        $peopleGroup->removeRolePerson($rolePerson);
        $peopleGroup->setNbPeople($nbPeople - 1);

        $this->manager->flush();

        return [
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => $person->getFullname().' est retiré'.Grammar::gender($person->getGender()).' du groupe.',
            'data' => $nbPeople - 1,
        ];
    }

    /**
     *  Vérifie si la personne est déjà rattachée à ce groupe.
     */
    protected function personExists(PeopleGroup $peopleGroup, Person $person): bool
    {
        return 0 != $this->rolePersonRepo->count([
            'person' => $person->getId(),
            'peopleGroup' => $peopleGroup->getId(),
        ]);
    }
}
