<?php

namespace App\Service\SupportGroup;

use App\Entity\AccommodationGroup;
use App\Entity\EvaluationGroup;
use App\Entity\EvaluationPerson;
use App\Entity\GroupPeople;
use App\Entity\Person;
use App\Entity\Rdv;
use App\Entity\RolePerson;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Entity\User;
use App\Form\Utils\Choices;
use App\Repository\ContributionRepository;
use App\Repository\DocumentRepository;
use App\Repository\EvaluationGroupRepository;
use App\Repository\NoteRepository;
use App\Repository\RdvRepository;
use App\Repository\ReferentRepository;
use App\Repository\ServiceRepository;
use App\Repository\SubServiceRepository;
use App\Repository\SupportGroupRepository;
use App\Service\Grammar;
use App\Service\hydrateObjectWithArray;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SupportManager
{
    use hydrateObjectWithArray;

    private $session;
    private $repoSupportGroup;
    private $repoService;
    private $repoSubService;
    private $repoEvaluationGroup;
    private $manager;
    private $cache;

    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $manager,
        SupportGroupRepository $repoSupportGroup,
        ServiceRepository $repoService,
        SubServiceRepository $repoSubService,
        EvaluationGroupRepository $repoEvaluationGroup)
    {
        $this->session = $session;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoService = $repoService;
        $this->repoSubService = $repoSubService;
        $this->repoEvaluationGroup = $repoEvaluationGroup;

        $this->manager = $manager;
        $this->cache = new FilesystemAdapter();
    }

    /**
     * Donne un nouveau suivi paramétré.
     */
    public function getNewSupportGroup(GroupPeople $groupPeople, Request $request)
    {
        $supportGroup = (new SupportGroup())->setGroupPeople($groupPeople);

        $serviceId = $request->request->get('support')['service'];

        if ((int) $serviceId) {
            $supportGroup->setService($this->repoService->find($serviceId));
        }

        return $supportGroup;
    }

    /**
     * Créé un nouveau suivi.
     */
    public function create(GroupPeople $groupPeople, SupportGroup $supportGroup, bool $cloneSupport = false): bool
    {
        if ($this->activeSupportExists($groupPeople, $supportGroup)) {
            return false;
        }

        $supportGroup->setGroupPeople($groupPeople)
            ->setCoefficient($supportGroup->getDevice()->getCoefficient());

        // Si l'utilisateur veut récupérer les informations du précédent suivi, alors clone l'évaluation sociale et les documents existants.
        if (true === $cloneSupport) {
            $this->cloneSupport($supportGroup);
        }

        $serviceId = $supportGroup->getService()->getId();

        // Contrôle le service du suivi
        if ($serviceId === Service::SERVICE_AVDL_ID) {
            $supportGroup = (new AvdlService())->updateSupportGroup($supportGroup);
        }
        if ($serviceId === Service::SERVICE_PASH_ID) {
            $supportGroup = (new HotelSupportService())->updateSupportGroup($supportGroup);
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
    public function createSupportPerson(RolePerson $rolePerson, SupportGroup $supportGroup): SupportPerson
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
            $this->addFlash('warning', $supportPerson->getPerson()->getFullname().' : la date de début de suivi retenue est sa date de naissance.');
        }

        $this->manager->persist($supportPerson);

        $supportGroup->setNbPeople($supportGroup->getNbPeople() + 1);

        return $supportPerson;
    }

    /**
     * Met à jour le suivi social du groupe.
     */
    public function update(SupportGroup $supportGroup): void
    {
        $supportGroup->setUpdatedAt(new \DateTime());
        $serviceId = $supportGroup->getService()->getId();

        // Vérifie le service du suivi
        if ($serviceId === Service::SERVICE_AVDL_ID) {
            $supportGroup = (new AvdlService())->updateSupportGroup($supportGroup);
        }
        if ($serviceId === Service::SERVICE_PASH_ID) {
            $supportGroup = (new HotelSupportService())->updateSupportGroup($supportGroup);
        }

        $this->updateSupportPeople($supportGroup);
        $this->updateAccommodationGroup($supportGroup);

        $this->manager->flush();

        $this->discache($supportGroup);

        $this->addFlash('success', 'Le suivi social est mis à jour.');
    }

    /**
     * Crée une copie d'un suivi social.
     */
    public function cloneSupport(SupportGroup $supportGroup): ?SupportGroup
    {
        $oldSupport = $this->repoSupportGroup->findLastSupport($supportGroup);

        if (null === $oldSupport) {
            return null;
        }

        foreach ($oldSupport->getDocuments() as $document) {
            $newDocument = (clone $document)->setSupportGroup($supportGroup);
            $supportGroup->getDocuments()->add($newDocument);
        }

        $lastNote = $oldSupport->getNotes()->last();
        $lastEvaluation = $oldSupport->getEvaluationsGroup()->last();

        if ($lastNote) {
            $note = (clone $lastNote)->setSupportGroup($supportGroup);
            $supportGroup->getNotes()->add($note);
        }
        if (0 === $supportGroup->getEvaluationsGroup()->count() && $lastEvaluation) {
            $evaluationGroup = (clone $lastEvaluation)->setSupportGroup($supportGroup);
            $supportGroup->getEvaluationsGroup()->add($evaluationGroup);
        }

        return $supportGroup;
    }

    /**
     * Donne le suivi social complet.
     */
    public function getFullSupportGroup(int $id): ?SupportGroup
    {
        return $this->cache->get(SupportGroup::CACHE_FULLSUPPORT_KEY.$id, function (CacheItemInterface $item) use ($id) {
            $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

            return $this->repoSupportGroup->findFullSupportById($id);
        });
    }

    /**
     * Donne le suivi social.
     */
    public function getSupportGroup(int $id): ?SupportGroup
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_KEY.$id, function (CacheItemInterface $item) use ($id) {
            $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

            return $this->repoSupportGroup->findSupportById($id);
        });
    }

    /**
     * Donne l'évaluation sociale complète.
     */
    public function getEvaluation(SupportGroup $supportGroup): ?EvaluationGroup
    {
        return $this->cache->get(EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

            return $this->repoEvaluationGroup->findEvaluationById($supportGroup);
        });
    }

    /**
     * Donne le référent du suivi social.
     */
    public function getReferents(SupportGroup $supportGroup, ReferentRepository $repoReferent)
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_REFERENT_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup, $repoReferent) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $repoReferent->findReferents($supportGroup->getGroupPeople());
        });
    }

    /**
     * Donne le nombre de notes du suivi social.
     */
    public function getNbNotes(SupportGroup $supportGroup, NoteRepository $repoNote): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_NOTES_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup, $repoNote) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $repoNote->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * Donne le nombre de RDVs du suivi social.
     */
    public function getNbRdvs(SupportGroup $supportGroup, RdvRepository $repoRdv): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_RDVS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup, $repoRdv) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $repoRdv->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * Donne le dernier RDV du suivi social.
     */
    public function getLastRdvs(SupportGroup $supportGroup, RdvRepository $repoRdv): ?Rdv
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_LAST_RDV_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup, $repoRdv) {
            $item->expiresAfter(\DateInterval::createFromDateString('12 hours'));

            return $repoRdv->findLastRdvFromSupport($supportGroup->getId());
        });
    }

    /**
     * Donne le RDV suivant du suivi social.
     */
    public function getNextRdvs(SupportGroup $supportGroup, RdvRepository $repoRdv): ?Rdv
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NEXT_RDV_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup, $repoRdv) {
            $item->expiresAfter(\DateInterval::createFromDateString('12 hours'));

            return $repoRdv->findNextRdvFromSupport($supportGroup->getId());
        });
    }

    /**
     * Donne le nombre de documents du suivi social.
     */
    public function getNbDocuments(SupportGroup $supportGroup, DocumentRepository $repoDocument): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_DOCUMENTS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup, $repoDocument) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $repoDocument->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * Donne le nombre de contributions du suivi social.
     */
    public function getNbContributions(SupportGroup $supportGroup, ContributionRepository $repoContribution): ?int
    {
        if (!$supportGroup->getAccommodationGroups()) {
            return  null;
        }

        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_CONTRIBUTIONS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup, $repoContribution) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $repoContribution->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * Vide le cache du suivi social et des indicateurs du service.
     */
    public function discache(SupportGroup $supportGroup): bool
    {
        if ($supportGroup->getReferent()) {
            $this->cache->deleteItem(User::CACHE_USER_SUPPORTS_KEY.$supportGroup->getReferent()->getId());
        }

        return $this->cache->deleteItems([
            SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId(),
            Service::CACHE_INDICATORS_KEY.$supportGroup->getService()->getId(),
        ]);
    }

    /**
     * Initialise l'évaluation sociale.
     */
    protected function initEvaluation(EvaluationGroup $evaluationGroup, NormalizerInterface $normalizer): void
    {
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

    /**
     * Vérifie la cohérence des données du suivi social.
     *
     * @return void
     */
    protected function checkSupportGroup(SupportGroup $supportGroup): void
    {
        // Vérifie que le nombre de personnes suivies correspond à la composition familiale du groupe
        $nbPeople = $supportGroup->getGroupPeople()->getNbPeople();
        $nbSupportPeople = $supportGroup->getSupportPeople()->count();
        $nbActiveSupportPeople = 0;

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $supportPerson->getEndDate() === null ? ++$nbActiveSupportPeople : null;
        }

        if ($nbSupportPeople != $nbPeople && $nbActiveSupportPeople != $nbPeople) {
            $this->addFlash('warning', 'Attention, le nombre de personnes actuellement suivies 
                ne correspond pas à la composition familiale du groupe ('.$nbPeople.' personnes).<br/> 
                Cliquez sur le buton <b>Modifier</b> pour ajouter les personnes au suivi.');
        }

        if ($supportGroup->getDevice() && $supportGroup->getDevice()->getAccommodation() === Choices::YES) {
            // Vérifie qu'il y a un hébergement créé
            if (0 === $supportGroup->getAccommodationGroups()->count()) {
                $this->addFlash('warning', 'Attention, aucun hébergement n\'est enregistré pour ce suivi.');
            } else {
                // Vérifie que le nombre de personnes suivies correspond au nombre de personnes hébergées
                $nbAccommodationPeople = 0;
                foreach ($supportGroup->getAccommodationGroups() as $accommodationGroup) {
                    if (null === $accommodationGroup->getEndDate()) {
                        foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
                            if (null === $accommodationPerson->getEndDate()) {
                                ++$nbAccommodationPeople;
                            }
                        }
                    }
                }
                if (!$supportGroup->getEndDate() && $nbActiveSupportPeople != $nbAccommodationPeople) {
                    $this->addFlash('warning', 'Attention, le nombre de personnes actuellement suivies ('.$nbActiveSupportPeople.') 
                    ne correspond pas au nombre de personnes hébergées ('.$nbAccommodationPeople.').<br/> 
                    Allez dans l\'onglet <b>Hébergement</b> pour ajouter les personnes à l\'hébergement.');
                }
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
            // Si personne seule dans le suivi ou si lad ate de début de suivi est vide, copie la date de début de suivi
            if (1 === $nbPeople || null === $supportPerson->getStartDate()) {
                $supportPerson
                    ->setStatus($supportGroup->getStatus())
                    ->setStartDate($supportGroup->getStartDate());
            }
            if ($supportPerson->getEndDate()) {
                $supportPerson->setStatus(SupportGroup::STATUS_ENDED);
            }
            if (null === $supportPerson->getStatus() && $supportPerson->getEndDate()) {
                $supportPerson->setStatus($supportGroup->getStatus());
            }
            if (null === $supportPerson->getEndStatus() && $supportPerson->getEndDate()) {
                $supportPerson->setEndStatus($supportGroup->getEndStatus());
            }
            if (null === $supportPerson->getEndStatusComment() && $supportPerson->getEndDate()) {
                $supportPerson->setEndStatusComment($supportGroup->getEndStatusComment());
            }

            // Vérifie si la date de suivi n'est pas antérieure à la date de naissance
            $person = $supportPerson->getPerson();
            if ($supportPerson->getStartDate() && $person && $supportPerson->getStartDate() < $person->getBirthdate()) {
                // Si c'est le cas, on prend en compte la date de naissance
                $supportPerson->setStartDate($person->getBirthdate());
                // $this->addFlash('light', $supportPerson->getPerson()->getFullname().' : la date de début de suivi retenue est sa date de naissance.');
            }
        }
    }

    /**
     * Met à jour la prise en charge du groupe.
     */
    protected function updateAccommodationGroup(SupportGroup $supportGroup): void
    {
        // Si le statut du suivi est égal à terminé et si  "Fin d'hébergement" coché, alors met à jour la prise en charge
        if (4 === $supportGroup->getStatus() && $supportGroup->getEndAccommodation()) {
            foreach ($supportGroup->getAccommodationGroups() as $accommodationGroup) {
                if (!$accommodationGroup->getEndDate()) {
                    $accommodationGroup->getEndDate() === null ? $accommodationGroup->setEndDate($supportGroup->getEndDate()) : null;
                    $accommodationGroup->getEndReason() === null ? $accommodationGroup->setEndReason(1) : null;

                    $this->updateAccommodationPeople($accommodationGroup);
                }
            }
        }
    }

    /**
     * Met à jour la prise en charge des personnes du groupe.
     */
    protected function updateAccommodationPeople(AccommodationGroup $accommodationGroup): void
    {
        foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
            $supportPerson = $accommodationPerson->getSupportPerson();
            $person = $supportPerson->getPerson();

            $accommodationPerson->getEndDate() === null ? $accommodationPerson->setEndDate($supportPerson->getEndDate()) : null;
            $accommodationPerson->getEndReason() === null ? $accommodationPerson->setEndReason(1) : null;

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
     * Ajoute un message flash.
     */
    protected function addFlash(string $alert, string $msg)
    {
        $this->session->getFlashBag()->add($alert, $msg);
    }
}
