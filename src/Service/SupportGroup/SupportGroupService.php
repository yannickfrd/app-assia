<?php

namespace App\Service\SupportGroup;

use App\Entity\User;
use App\Entity\Person;
use App\Service\Grammar;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Entity\EvaluationPerson;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EvaluationGroupRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SupportGroupService
{
    private $container;
    private $manager;

    public function __construct(ContainerInterface $container, EntityManagerInterface $manager)
    {
        $this->container = $container;
        $this->manager = $manager;
    }

    /**
     * Donne un nouveau suivi paramétré.
     */
    public function getNewSupportGroup(User $user)
    {
        return  (new SupportGroup())
            ->setStatus(2)
            ->setReferent($user);
    }

    /**
     * Créé un nouveau suivi.
     */
    public function createSupportGroup(GroupPeople $groupPeople, SupportGroup $supportGroup)
    {
        if ($this->activeSupportExists($groupPeople, $supportGroup)) {
            return false;
        }

        $supportGroup->setGroupPeople($groupPeople);

        $this->manager->persist($supportGroup);

        // Créé un suivi social individuel pour chaque personne du groupe
        foreach ($groupPeople->getRolePeople() as $rolePerson) {
            $this->createSupportPerson($rolePerson, $supportGroup);
        }

        $this->manager->flush();
    }

    /**
     * Crée un suivi individuel.
     */
    public function createSupportPerson(RolePerson $rolePerson, SupportGroup $supportGroup)
    {
        $supportPerson = (new SupportPerson())
            ->setSupportGroup($supportGroup)
            ->setPerson($rolePerson->getPerson())
            ->setHead($rolePerson->getHead())
            ->setRole($rolePerson->getRole())
            ->setStatus($supportGroup->getStatus())
            ->setStartDate($supportGroup->getStartDate())
            ->setEndDate($supportGroup->getEndDate())
            ->setEndStatus($supportGroup->getEndStatus())
            ->setEndStatusComment($supportGroup->getEndStatusComment());

        $this->manager->persist($supportPerson);

        return $supportPerson;
    }

    /**
     * Met à jour le suivi social de la personne.
     */
    public function updateSupportPeople(SupportGroup $supportGroup)
    {
        $nbPeople = count($supportGroup->getSupportPeople());
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if (1 == $nbPeople) {
                $supportPerson->setStartDate($supportGroup->getStartDate());
            }
            if (1 == $nbPeople || !$supportPerson->getEndDate()) {
                $supportPerson->setStatus($supportGroup->getStatus());
                $supportPerson->setEndDate($supportGroup->getEndDate());
                $supportPerson->setEndStatus($supportGroup->getEndStatus());
                $supportPerson->setEndStatusComment($supportGroup->getEndStatusComment());
            }
        }
    }

    public function addPeopleInSupport(SupportGroup $supportGroup, EvaluationGroupRepository $repo)
    {
        $addPeople = false;

        foreach ($supportGroup->getGroupPeople()->getRolePeople() as $rolePerson) {
            if (!$this->personIsInSupport($rolePerson->getPerson(), $supportGroup)) {
                $supportPerson = $this->createSupportPerson($rolePerson, $supportGroup);

                $evaluationGroup = $repo->findLastEvaluationFromSupport($supportGroup);

                if ($evaluationGroup) {
                    $evaluationPerson = (new EvaluationPerson())
                        ->setEvaluationGroup($evaluationGroup)
                        ->setSupportPerson($supportPerson);

                    $this->manager->persist($evaluationPerson);
                }

                $this->container->get('session')->getFlashBag()->add('success', $rolePerson->getPerson()->getFullname().' est ajouté'.Grammar::gender($supportPerson->getPerson()->getGender()).' au suivi.');

                $addPeople = true;
            }
        }
        $this->manager->flush();

        return $addPeople;
    }

    /**
     * Vérifie si un suivi social est déjà en cours dans le même service.
     */
    protected function activeSupportExists(GroupPeople $groupPeople, SupportGroup $supportGroup): ?SupportGroup
    {
        return $this->repoSupportGroup->findOneBy([
            'groupPeople' => $groupPeople,
            'status' => 2,
            'service' => $supportGroup->getService(),
        ]);
    }

    /**
     * Vérifie si la personne est déjà dans le suivi social.
     */
    protected function personIsInSupport(Person $person, SupportGroup $supportGroup): bool
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($person == $supportPerson->getPerson()) {
                return true;
            }
        }

        return false;
    }
}
