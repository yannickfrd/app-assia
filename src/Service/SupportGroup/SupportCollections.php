<?php

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Event\Rdv;
use App\Entity\Organization\Referent;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Event\RdvRepository;
use App\Repository\Event\TaskRepository;
use App\Repository\Organization\ReferentRepository;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\PaymentRepository;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class SupportCollections
{
    private $cache;

    private $noteRepo;
    private $rdvRepo;
    private $taskRepo;
    private $documentRepo;
    private $paymentRepo;
    private $referentRepo;
    private $evaluationGroupRepo;

    public function __construct(
        NoteRepository $noteRepo,
        RdvRepository $rdvRepo,
        TaskRepository $taskRepo,
        DocumentRepository $documentRepo,
        PaymentRepository $paymentRepo,
        ReferentRepository $referentRepo,
        EvaluationGroupRepository $evaluationGroupRepo
    ) {
        $this->noteRepo = $noteRepo;
        $this->rdvRepo = $rdvRepo;
        $this->taskRepo = $taskRepo;
        $this->documentRepo = $documentRepo;
        $this->paymentRepo = $paymentRepo;
        $this->referentRepo = $referentRepo;
        $this->evaluationGroupRepo = $evaluationGroupRepo;

        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    /**
     * Donne l'évaluation sociale complète.
     */
    public function getEvaluation(SupportGroup $supportGroup): ?EvaluationGroup
    {
        return $this->cache->get(EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->evaluationGroupRepo->findEvaluationOfSupport($supportGroup->getId());
        });
    }

    /**
     * Donne les référents du suivi.
     *
     * @return Referent[]
     */
    public function getReferents(SupportGroup $supportGroup): array
    {
        $peopleGroup = $supportGroup->getPeopleGroup();

        return $this->cache->get(PeopleGroup::CACHE_GROUP_REFERENTS_KEY.$peopleGroup->getId(), function (CacheItemInterface $item) use ($peopleGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->referentRepo->findReferentsOfPeopleGroup($peopleGroup);
        });
    }

    /**
     * Donne le nombre de notes du suivi social.
     */
    public function getNbNotes(SupportGroup $supportGroup): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_NOTES_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->noteRepo->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * Donne le nombre de RDVs du suivi social.
     */
    public function getNbRdvs(SupportGroup $supportGroup): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_RDVS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->rdvRepo->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * Donne le nombre d'évenement du suivi social.
     */
    public function getNbTasks(SupportGroup $supportGroup): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_TASKS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->taskRepo->count([
                'supportGroup' => $supportGroup->getId(),
                'status' => false, ]
            );
        });
    }

    /**
     * Donne le dernier RDV du suivi social.
     */
    public function getLastRdvs(SupportGroup $supportGroup): ?Rdv
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_LAST_RDV_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('12 hours'));

            return $this->rdvRepo->findLastRdvOfSupport($supportGroup->getId());
        });
    }

    /**
     * Donne le RDV suivant du suivi social.
     */
    public function getNextRdvs(SupportGroup $supportGroup): ?Rdv
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NEXT_RDV_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('12 hours'));

            return $this->rdvRepo->findNextRdvOfSupport($supportGroup->getId());
        });
    }

    /**
     * Donne le nombre de documents du suivi social.
     */
    public function getNbDocuments(SupportGroup $supportGroup): int
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_DOCUMENTS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->documentRepo->count(['supportGroup' => $supportGroup->getId()]);
        });
    }

    /**
     * @return Payment[]
     */
    public function getAllPayments(SupportGroup $supportGroup): array
    {
        if (Choices::YES !== $supportGroup->getService()->getContribution()) {
            return [];
        }

        return $this->cache->get(SupportGroup::CACHE_SUPPORT_PAYMENTS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->paymentRepo->findPaymentsOfSupport($supportGroup);
        });
    }

    /**
     * @return Payment[]
     */
    public function getPaymentsOrderedByStartDate(SupportGroup $supportGroup): array
    {
        if (Choices::YES !== $supportGroup->getService()->getContribution()) {
            return [];
        }

        return $this->cache->get(SupportGroup::CACHE_SUPPORT_PAYMENTS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->paymentRepo->findPaymentsOfSupportOrderedByStartDate($supportGroup);
        });
    }

    /**
     * Donne le nombre de paiements du suivi social.
     */
    public function getNbPayments(SupportGroup $supportGroup): ?int
    {
        if (Choices::YES !== $supportGroup->getService()->getContribution()) {
            return null;
        }

        return $this->cache->get(SupportGroup::CACHE_SUPPORT_NB_PAYMENTS_KEY.$supportGroup->getId(), function (CacheItemInterface $item) use ($supportGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->paymentRepo->count(['supportGroup' => $supportGroup->getId()]);
        });
    }
}
