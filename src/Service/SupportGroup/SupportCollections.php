<?php

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\ReferentRepository;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\PaymentRepository;
use App\Repository\Support\RdvRepository;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class SupportCollections
{
    private $cache;

    private $noteRepository;
    private $rdvRepository;
    private $documentRepository;
    private $paymentRepository;
    private $referentRepository;
    private $evaluationGroupRepository;

    public function __construct(
        NoteRepository $noteRepository,
        RdvRepository $rdvRepository,
        DocumentRepository $documentRepository,
        PaymentRepository $paymentRepository,
        ReferentRepository $referentRepository,
        EvaluationGroupRepository $evaluationGroupRepository
    ) {
        $this->noteRepository = $noteRepository;
        $this->rdvRepository = $rdvRepository;
        $this->documentRepository = $documentRepository;
        $this->paymentRepository = $paymentRepository;
        $this->referentRepository = $referentRepository;
        $this->evaluationGroupRepository = $evaluationGroupRepository;

        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    /**
     * Donne l'évaluation sociale complète.
     */
    public function getEvaluation(SupportGroup $supportGroup): ?EvaluationGroup
    {
        return $this->cache->get(EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->evaluationGroupRepository->findEvaluationOfSupport($supportGroup->getId());
        });
    }

    /**
     * Donne les référents du suivi.
     */
    public function getReferents(SupportGroup $supportGroup)
    {
        $peopleGroup = $supportGroup->getPeopleGroup();

        return $this->cache->get(PeopleGroup::CACHE_GROUP_REFERENTS_KEY.$peopleGroup->getId(), function (CacheItemInterface $item) use ($peopleGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->referentRepository->findReferentsOfPeopleGroup($peopleGroup);
        });
    }

    /**
     * Donne le nombre de notes du suivi social.
     */
    public function getNbNotes(SupportGroup $supportGroup): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_NOTES_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->noteRepository->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * Donne le nombre de RDVs du suivi social.
     */
    public function getNbRdvs(SupportGroup $supportGroup): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_RDVS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->rdvRepository->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * Donne le dernier RDV du suivi social.
     */
    public function getLastRdvs(SupportGroup $supportGroup): ?Rdv
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_LAST_RDV_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('12 hours'));

            return $this->rdvRepository->findLastRdvOfSupport($supportGroup->getId());
        });
    }

    /**
     * Donne le RDV suivant du suivi social.
     */
    public function getNextRdvs(SupportGroup $supportGroup): ?Rdv
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NEXT_RDV_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('12 hours'));

            return $this->rdvRepository->findNextRdvOfSupport($supportGroup->getId());
        });
    }

    /**
     * Donne le nombre de documents du suivi social.
     */
    public function getNbDocuments(SupportGroup $supportGroup): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_DOCUMENTS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->documentRepository->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * Donne le nombre de paiements du suivi social.
     */
    public function getNbPayments(SupportGroup $supportGroup): ?int
    {
        if (!$supportGroup->getPlaceGroups()) {
            return null;
        }

        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_PAYMENTS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->paymentRepository->count(['supportGroup' => $supportGroup->getId()]);
        });
    }
}
