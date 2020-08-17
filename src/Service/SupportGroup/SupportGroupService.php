<?php

namespace App\Service\SupportGroup;

use App\Entity\User;
use App\Entity\Person;
use App\Entity\Service;
use App\Service\Grammar;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Entity\EvaluationGroup;
use App\Entity\EvaluationPerson;
use App\Entity\AccommodationGroup;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use App\Repository\EvaluationGroupRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SupportGroupService
{
    private $container;
    private $repoSupportGroup;
    private $repoEvaluationGroup;
    private $manager;
    private $avdlService;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $manager,
        SupportGroupRepository $repoSupportGroup,
        EvaluationGroupRepository $repoEvaluationGroup,
        AvdlService $avdlService)
    {
        $this->container = $container;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoEvaluationGroup = $repoEvaluationGroup;
        $this->manager = $manager;
        $this->avdlService = $avdlService;
        $this->cache = new FilesystemAdapter();
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
     * Donne le suivi social complet.
     */
    public function getFullSupportGroup(int $id): ?SupportGroup
    {
        $cacheSupport = $this->cache->getItem('support_group_full.'.$id);

        if (!$cacheSupport->isHit()) {
            $supportGroup = $this->repoSupportGroup->findFullSupportById($id);

            $cacheSupport->set($supportGroup);
            $cacheSupport->expiresAfter(365 * 24 * 60 * 60);
            $this->cache->save($cacheSupport);
        }
        $supportGroup = $cacheSupport->get();

        $this->checkSupportGroup($supportGroup);

        return $supportGroup;
    }

    /**
     * Donne le suivi social complet.
     */
    public function getSupportGroup(int $id): ?SupportGroup
    {
        $cacheSupport = $this->cache->getItem('support_group.'.$id);

        if (!$cacheSupport->isHit()) {
            $supportGroup = $this->repoSupportGroup->findSupportById($id);

            $cacheSupport->set($supportGroup);
            $cacheSupport->expiresAfter(365 * 24 * 60 * 60);
            $this->cache->save($cacheSupport);
        }

        return $cacheSupport->get();
    }

    /**
     * Donne l'évaluation sociale complète.
     */
    public function getEvaluation(int $id): ?EvaluationGroup
    {
        $cacheEvaluation = $this->cache->getItem('support_group.evaluation.'.$id);

        if (!$cacheEvaluation->isHit()) {
            $cacheEvaluation->set($this->repoEvaluationGroup->findEvaluationById($id));
            $this->cache->save($cacheEvaluation);
        }

        return $cacheEvaluation->get();
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

        $this->discache($supportGroup);
    }

    /**
     * Crée une copie d'un suivi social.
     */
    public function cloneSupport(SupportGroup $supportGroup, $user, NormalizerInterface $normalizer): SupportGroup
    {
        $newSupportGroup = clone $supportGroup;

        $newSupportGroup
            ->setReferent($user)
            ->setReferent2(null)
            ->setStatus(2)
            ->setStartDate(null)
            ->setEndDate(null)
            ->setTheoreticalEndDate(null)
            ->setEndStatus(null)
            ->setEndStatusComment(null)
            ->setCreatedBy($user)
            ->setUpdatedBy($user);

        $this->manager->persist($newSupportGroup);

        /** @var EvaluationGroup */
        $evaluationGroup = $newSupportGroup->getEvaluationsGroup()->last();

        if ($evaluationGroup) {
            $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();
            $evalHousingGroup = $evaluationGroup->getEvalHousingGroup();

            $initEvalGroup = $evaluationGroup->getInitEvalGroup();

            if ($evalBudgetGroup) {
                $initEvalGroup
            ->setResourcesGroupAmt($evalBudgetGroup->getResourcesGroupAmt())
            ->setDebtsGroupAmt($evalBudgetGroup->getDebtsGroupAmt());
            }
            if ($evalHousingGroup) {
                $initEvalGroup
            ->setHousingStatus($evalHousingGroup->getHousingStatus())
            ->setSiaoRequest($evalHousingGroup->getSiaoRequest())
            ->setSocialHousingRequest($evalHousingGroup->getSocialHousingRequest());
            }

            foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
                /** @var EvaluationPerson */
                $evaluationPerson = $evaluationPerson;
                $evalAdminPerson = $evaluationPerson->getEvalAdmPerson();
                $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();
                $evalProfPerson = $evaluationPerson->getEvalProfPerson();
                $evalSocialPerson = $evaluationPerson->getEvalSocialPerson();

                $initEvalPerson = $evaluationPerson->getInitEvalPerson();

                $initEvalPerson->setPaperType($evalAdminPerson ? $evalAdminPerson->getPaperType() : null);

                if ($evalSocialPerson) {
                    $arrayEvalSocialPerson = $normalizer->normalize($evalSocialPerson, null, [
                    AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'evaluationPerson'],
                ]);
                    $this->hydrateObjectWithArray($initEvalPerson, $arrayEvalSocialPerson);
                }
                if ($evalProfPerson) {
                    $initEvalPerson
                    ->setProfStatus($evalProfPerson->getProfStatus())
                    ->setContractType($evalProfPerson->getContractType());
                }
                if ($evalBudgetPerson) {
                    $arrayEvalBudgetPerson = $normalizer->normalize($evalBudgetPerson, null, [
                    AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'evaluationPerson'],
                ]);
                    $this->hydrateObjectWithArray($initEvalPerson, $arrayEvalBudgetPerson);
                }
            }
        }

        return $newSupportGroup;
    }

    protected function hydrateObjectWithArray($object, $array)
    {
        foreach ($array as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists($object, $method)) {
                $object->$method($value);
            }
        }

        return $object;
    }

    /**
     * Vérifie la cohérence des données du suivi social.
     *
     * @param SupportGroup $supportGroup
     *
     * @return void
     */
    protected function checkSupportGroup(SupportGroup $supportGroup)
    {
        // Vérifie que le nombre de personnes suivies correspond à la composition familiale du groupe
        $nbPeople = $supportGroup->getGroupPeople()->getNbPeople();
        $nbSupportPeople = $supportGroup->getSupportPeople()->count();
        $nbActiveSupportPeople = 0;

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $supportPerson->getEndDate() == null ? ++$nbActiveSupportPeople : null;
        }

        if ($nbSupportPeople != $nbPeople && $nbActiveSupportPeople != $nbPeople) {
            $this->addFlash('warning', 'Attention, le nombre de personnes actuellement suivies 
                ne correspond pas à la composition familiale du groupe ('.$nbPeople.' personnes).<br/> 
                Cliquez sur le buton <b>Modifier</b> pour ajouter les personnes au suivi.');
        }

        // Vérifie qu'il y a un hébergement créé
        if (1 == $supportGroup->getDevice()->getAccommodation() && 0 == $supportGroup->getAccommodationGroups()->count()) {
            $this->addFlash('warning', 'Attention, aucun hébergement n\'est enregistré pour ce suivi.');
        } else {
            // Vérifie que le nombre de personnes suivies correspond au nombre de personnes hébergées
            $nbAccommodationPeople = 0;
            foreach ($supportGroup->getAccommodationGroups() as $accommodationGroup) {
                if (null == $accommodationGroup->getEndDate()) {
                    foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
                        if (null == $accommodationPerson->getEndDate()) {
                            ++$nbAccommodationPeople;
                        }
                    }
                }
            }
            if (!$supportGroup->getEndDate() && 1 == $supportGroup->getDevice()->getAccommodation() && $nbActiveSupportPeople != $nbAccommodationPeople) {
                $this->addFlash('warning', 'Attention, le nombre de personnes actuellement suivies ('.$nbActiveSupportPeople.') 
                    ne correspond pas au nombre de personnes hébergées ('.$nbAccommodationPeople.').<br/> 
                    Allez dans l\'onglet <b>Hébergement</b> pour ajouter les personnes à l\'hébergement.');
            }
        }
    }

    /**
     * Met à jour les suivis sociales individuelles des personnes.
     */
    protected function updateSupportPeople(SupportGroup $supportGroup): void
    {
        $nbPeople = $supportGroup->getSupportPeople()->count();

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            // Si personne seule dans le suivi ou si ladate de début de suivi est vide, copie la date de début de suivi
            if (1 == $nbPeople || null == $supportPerson->getStartDate()) {
                $supportPerson
                    ->setStatus($supportGroup->getStatus())
                    ->setStartDate($supportGroup->getStartDate());
            }
            if ($supportPerson->getEndDate()) {
                $supportPerson->setStatus(SupportGroup::STATUS_ENDED);
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
                $this->addFlash('warning', 'La date de début de suivi ne peut pas être antérieure à la date de naissance de la personne ('.$supportPerson->getPerson()->getFullname().').');
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
                $this->addFlash('warning', 'La date de début d\'hébergement ne peut pas être antérieure à la date de naissance de la personne ('.$accommodationPerson->getPerson()->getFullname().').');
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

                $this->addFlash('success', $rolePerson->getPerson()->getFullname().' est ajouté'.Grammar::gender($supportPerson->getPerson()->getGender()).' au suivi.');

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
    public function discache(SupportGroup $supportGroup): void
    {
        $cacheSupport = $this->cache->getItem('support_group_full.'.$supportGroup->getId());
        $this->cache->deleteItem($cacheSupport->getKey());
    }

    /**
     * Ajoute un message flash.
     */
    protected function addFlash(string $alert, string $msg)
    {
        $this->container->get('session')->getFlashBag()->add($alert, $msg);
    }
}
