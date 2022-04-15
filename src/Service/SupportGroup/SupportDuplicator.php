<?php

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Organization\Service;
use App\Entity\Support\Document;
use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Evaluation\EvaluationPersonRepository;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Document\DocumentManager;
use App\Service\Evaluation\EvaluationCompletionChecker;
use App\Service\Evaluation\EvaluationDuplicator;
use App\Service\Note\NoteManager;
use Doctrine\ORM\EntityManagerInterface;

class SupportDuplicator
{
    private $em;

    private $evaluationDuplicator;
    private $evaluationCompletionChecker;
    private $supportGroupRepo;
    private $supportPersonRepo;
    private $evaluationGroupRepo;
    private $evaluationPersonRepo;
    private $noteRepo;
    private $documentRepo;

    /** @var EvaluationGroup|null */
    private $evaluationGroup = null;

    public function __construct(
        EntityManagerInterface $em,
        EvaluationDuplicator $evaluationDuplicator,
        EvaluationCompletionChecker $evaluationCompletionChecker,
        SupportGroupRepository $supportGroupRepo,
        SupportPersonRepository $supportPersonRepo,
        EvaluationGroupRepository $evaluationGroupRepo,
        EvaluationPersonRepository $evaluationPersonRepo,
        NoteRepository $noteRepo,
        DocumentRepository $documentRepo
    ) {
        $this->em = $em;
        $this->evaluationDuplicator = $evaluationDuplicator;
        $this->evaluationCompletionChecker = $evaluationCompletionChecker;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->supportPersonRepo = $supportPersonRepo;
        $this->evaluationGroupRepo = $evaluationGroupRepo;
        $this->evaluationPersonRepo = $evaluationPersonRepo;
        $this->noteRepo = $noteRepo;
        $this->documentRepo = $documentRepo;
    }

    public function duplicate(SupportGroup $supportGroup): ?SupportGroup
    {
        if ($this->duplicateSupportGroup($supportGroup)) {
            return $supportGroup;
        }

        if ($this->duplicateSupportPeople($supportGroup)) {
            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if ($this->evaluationGroup && 0 === $supportPerson->getEvaluations()->count()) {
                    $evaluationPerson = (new EvaluationPerson())
                        ->setEvaluationGroup($supportGroup->getEvaluationsGroup()->last())
                        ->setSupportPerson($supportPerson);

                    $this->em->persist($evaluationPerson);
                }
            }

            $supportGroup->setEvaluationScore($this->evaluationCompletionChecker->getScore($this->evaluationGroup)['score']);

            $this->em->flush();

            return $supportGroup;
        }

        return null;
    }

    /**
     * Crée une copie d'un suivi social.
     */
    public function duplicateSupportGroup(SupportGroup $supportGroup): ?SupportGroup
    {
        $oldSupportGroup = $this->supportGroupRepo->findLastSupport($supportGroup);

        if (null === $oldSupportGroup) {
            return null;
        }

        $this->duplicateEvaluation($supportGroup, $oldSupportGroup);
        $this->duplicateNotes($supportGroup, $oldSupportGroup);
        $this->duplicateDocuments($supportGroup, $oldSupportGroup);

        $this->em->flush();

        return $supportGroup;
    }

    private function duplicateEvaluation(SupportGroup $newSupportGroup, SupportGroup $oldSupportGroup): void
    {
        $oldEvaluation = $this->evaluationGroupRepo->findEvaluationOfSupport($oldSupportGroup->getId());

        if ($oldEvaluation && 0 === $newSupportGroup->getEvaluationsGroup()->count()) {
            $evaluationGroup = (clone $oldEvaluation)->setSupportGroup($newSupportGroup);

            if (Service::SERVICE_TYPE_HOTEL === $newSupportGroup->getService()->getType()
                && $oldEvaluation->getEvalHotelLifeGroup()) {
                $evaluationGroup->setEvalHotelLifeGroup(clone $oldEvaluation->getEvalHotelLifeGroup());
            }

            $this->evaluationDuplicator->createEvalInitGroup($newSupportGroup, $evaluationGroup);

            $newSupportGroup->getEvaluationsGroup()->add($evaluationGroup);
            // Change the supportPerson in every evaluationPerson
            foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
                foreach ($newSupportGroup->getSupportPeople() as $newSupportPerson) {
                    if ($evaluationPerson->getSupportPerson()->getPerson()->getId() === $newSupportPerson->getPerson()->getId()) {
                        $newSupportPerson->addEvaluationPerson($evaluationPerson);

                        $this->evaluationDuplicator->createEvalInitPerson($newSupportPerson, $evaluationPerson);
                    }
                }
            }

            $newSupportGroup->setEvaluationScore($this->evaluationCompletionChecker->getScore($evaluationGroup)['score']);
        }
    }

    private function duplicateDocuments(SupportGroup $supportGroup, SupportGroup $oldSupportGroup): void
    {
        $documentsOfSupport = $this->documentRepo->findBy(['supportGroup' => $supportGroup]);

        foreach ($this->documentRepo->findBy(['supportGroup' => $oldSupportGroup]) as $oldDocument) {
            if (!$this->documentExists($oldDocument, $documentsOfSupport)) {
                $supportGroup->addDocument(clone $oldDocument);
            }
        }

        if ($supportGroup->getDocuments()->count() > 0) {
            DocumentManager::deleteCacheItems($supportGroup);
        }
    }

    /**
     * Check if the document exists already in the support.
     *
     * @param Document[] $documents
     */
    private function documentExists(Document $oldDocument, array $documents): bool
    {
        foreach ($documents as $document) {
            if ($oldDocument->getInternalFileName() === $document->getInternalFileName()) {
                return true;
            }
        }

        return false;
    }

    private function duplicateNotes(SupportGroup $supportGroup, SupportGroup $oldSupportGroup): void
    {
        $notesOfSupport = $this->noteRepo->findBy(['supportGroup' => $supportGroup]);

        foreach ($this->noteRepo->findBy(['supportGroup' => $oldSupportGroup]) as $oldNote) {
            if (!$this->noteExists($oldNote, $notesOfSupport)) {
                $supportGroup->addNote(clone $oldNote);
            }
        }

        if ($supportGroup->getNotes()->count() > 0) {
            NoteManager::deleteCacheItems($supportGroup->getNotes()->last());
        }
    }

    /**
     * Check if the note exists already in the support.
     *
     * @param Note[] $notes
     */
    private function noteExists(Note $oldNote, array $notes): bool
    {
        foreach ($notes as $note) {
            if ($oldNote->getCreatedAt()->format('U') === $note->getCreatedAt()->format('U')) {
                return true;
            }
        }

        return false;
    }

    private function duplicateSupportPeople(SupportGroup $supportGroup): ?SupportGroup
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $oldSupportPerson = $this->supportPersonRepo->findLastSupport($supportPerson);

            if (null === $oldSupportPerson) {
                continue;
            }

            $oldEvaluationPerson = $this->evaluationPersonRepo->findEvaluationOfSupportPerson($oldSupportPerson->getId());

            if ($oldEvaluationPerson && 0 === $supportGroup->getEvaluationsGroup()->count()
                && null === $this->evaluationGroup) {
                $this->evaluationGroup = $this->evaluationDuplicator->cloneEvaluationGroup(
                    $supportGroup,
                    $oldEvaluationPerson->getEvaluationGroup()
                );
            } elseif ($supportGroup->getEvaluationsGroup()->count()) {
                $this->evaluationGroup = $supportGroup->getEvaluationsGroup()->last();
            }

            if ($oldEvaluationPerson && 0 === $supportPerson->getEvaluations()->count()) {
                $evaluationPerson = (clone $oldEvaluationPerson);
                $evaluationPerson->setEvaluationGroup($this->evaluationGroup);

                $supportPerson->addEvaluationPerson($evaluationPerson);
            }
        }

        if ($this->evaluationGroup) {
            $supportGroup->addEvaluationGroup($this->evaluationGroup);

            return $supportGroup;
        }

        return null;
    }
}
