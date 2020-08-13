<?php

namespace App\Service\SupportGroup;

use App\Entity\AccommodationGroup;
use App\Entity\User;
use App\Entity\Person;
use App\Entity\Service;
use App\Service\Grammar;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Entity\EvaluationPerson;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use App\Repository\EvaluationGroupRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SupportGroupService
{
    private $container;
    private $repo;
    private $manager;
    private $avdlService;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $manager,
        SupportGroupRepository $repo,
        AvdlService $avdlService)
    {
        $this->container = $container;
        $this->repo = $repo;
        $this->manager = $manager;
        $this->avdlService = $avdlService;
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
    public function create(GroupPeople $groupPeople, SupportGroup $supportGroup)
    {
        if ($this->activeSupportExists($groupPeople, $supportGroup)) {
            return false;
        }

        $supportGroup
            ->setGroupPeople($groupPeople)
            ->setCoefficient($supportGroup->getDevice()->getCoefficient());

        // Contrôle le service du suivi
        switch ($supportGroup->getService()->getId()) {
            case Service::SERVICE_AVDL_ID:
                $this->avdlService->updateSupportGroup($supportGroup);
            break;
        }

        $this->manager->persist($supportGroup);

        // Créé un suivi social individuel pour chaque personne du groupe
        foreach ($groupPeople->getRolePeople() as $rolePerson) {
            $this->createSupportPerson($rolePerson, $supportGroup);
        }

        $this->manager->flush();

        return true;
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

        $birthdate = $rolePerson->getPerson()->getBirthdate();

        if ($supportPerson->getStartDate() && $supportPerson->getStartDate() < $birthdate) {
            $supportPerson->setStartDate($birthdate);
        }

        $this->manager->persist($supportPerson);

        $supportGroup->setNbPeople($supportGroup->getNbPeople() + 1);

        return $supportPerson;
    }

    /**
     * Met à jour le suivi social du groupe.
     */
    public function update(SupportGroup $supportGroup)
    {
        $supportGroup->setUpdatedAt(new \DateTime());

        // Contrôle le service du suivi
        switch ($supportGroup->getService()->getId()) {
            case Service::SERVICE_AVDL_ID:
                $supportGroup = $this->avdlService->updateSupportGroup($supportGroup);
            break;
        }

        $this->updateSupportPeople($supportGroup);
        $this->updateAccommodationGroup($supportGroup);

        $this->discached($supportGroup);
    }

    /**
     * Met à jour les suivis sociales individuelles des personnes.
     */
    protected function updateSupportPeople(SupportGroup $supportGroup): void
    {
        $nbPeople = $supportGroup->getSupportPeople()->count();

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            // Si personne seule dans le suivi, copie la date de début de suivi
            if (1 == $nbPeople) {
                $supportPerson->setStartDate($supportGroup->getStartDate());
            }
            // Si la personne est seule ou si la date de fin de suivi et le motif de fin sont vides, copie toutes les infos sur la fin du suivi suivi
            if (1 == $nbPeople || (null == $supportPerson->getEndDate() && null == $supportPerson->getEndStatus())) {
                $supportPerson->setStatus($supportGroup->getStatus())
                    ->setEndDate($supportGroup->getEndDate())
                    ->setEndStatus($supportGroup->getEndStatus())
                    ->setEndStatusComment($supportGroup->getEndStatusComment());
            }

            // Vérifie si la date de suivi n'est pas antérieure à la date de naissance
            $person = $supportPerson->getPerson();
            if ($supportPerson->getStartDate() && $supportPerson->getStartDate() < $person->getBirthdate()) {
                // Si c'est le cas, on prend en compte la date de naissance
                $supportPerson->setStartDate($person->getBirthdate());
                $this->container->get('session')->getFlashBag()->add('warning', 'La date de début de suivi ne peut pas être antérieure à la date de naissance de la personne ('.$supportPerson->getPerson()->getFullname().').');
            }
        }
    }

    /**
     * Met à jour la prise en charge du groupe.
     */
    protected function updateAccommodationGroup(SupportGroup $supportGroup): void
    {
        // Si le statut du suivi est égal à terminé et si  "Fin d'hébergement" coché, alors met à jour la prise en charge
        if (4 == $supportGroup->getStatus() && $supportGroup->getEndAccommodation()) {
            foreach ($supportGroup->getAccommodationGroups() as $accommodationGroup) {
                if (!$accommodationGroup->getEndDate()) {
                    $accommodationGroup->getEndDate() == null ? $accommodationGroup->setEndDate($supportGroup->getEndDate()) : null;
                    $accommodationGroup->getEndReason() == null ? $accommodationGroup->setEndReason(1) : null;

                    $this->updateAccommodationPeople($accommodationGroup);
                }
            }
        }
    }

    /**
     * Met à jour la prise en charge des personnes du groupe.
     */
    protected function updateAccommodationPeople(AccommodationGroup $accommodationGroup)
    {
        foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
            $supportPerson = $accommodationPerson->getSupportPerson();
            $person = $supportPerson->getPerson();

            $accommodationPerson->getEndDate() == null ? $accommodationPerson->setEndDate($supportPerson->getEndDate()) : null;
            $accommodationPerson->getEndReason() == null ? $accommodationPerson->setEndReason(1) : null;

            if ($supportPerson->getStartDate() && $supportPerson->getStartDate() < $person->getBirthdate()) {
                $supportPerson->setStartDate($person->getBirthdate());
                $this->container->get('session')->getFlashBag()->add('warning', 'La date de début d\'hébergement ne peut pas être antérieure à la date de naissance de la personne ('.$accommodationPerson->getPerson()->getFullname().').');
            }
        }
    }

    /**
     * Ajoute les personnes au suivi.
     */
    public function addPeopleInSupport(SupportGroup $supportGroup, EvaluationGroupRepository $repoEvaluation): bool
    {
        $addPeople = false;

        foreach ($supportGroup->getGroupPeople()->getRolePeople() as $rolePerson) {
            if (!$this->personIsInSupport($rolePerson->getPerson(), $supportGroup)) {
                $supportPerson = $this->createSupportPerson($rolePerson, $supportGroup);

                $evaluationGroup = $repoEvaluation->findLastEvaluationFromSupport($supportGroup);

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
        return $this->repo->findOneBy([
            'groupPeople' => $groupPeople,
            'status' => SupportGroup::STATUS_IN_PROGRESS,
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

    /**
     * Vide l'item du suivi en cache.
     */
    protected function discached(SupportGroup $supportGroup): bool
    {
        $cache = new FilesystemAdapter();
        $cacheSupport = $cache->getItem('support_group'.$supportGroup->getId());
        if ($cacheSupport->isHit()) {
            $cache->deleteItem($cacheSupport->getKey());

            return true;
        }

        return false;
    }
}
