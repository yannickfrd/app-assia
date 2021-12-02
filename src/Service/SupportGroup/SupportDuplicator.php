<?php

namespace App\Service\SupportGroup;

use App\Entity\Support\Document;
use App\Entity\Organization\Service;
use App\Entity\Support\SupportGroup;
use App\Entity\Evaluation\InitEvalGroup;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Evaluation\EvaluationGroup;
use App\Repository\Support\NoteRepository;
use App\Entity\Evaluation\EvaluationPerson;
use Doctrine\Common\Collections\Collection;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Evaluation\EvaluationPersonRepository;

class SupportDuplicator
{
    private $em;

    private $supportGroupRepo;
    private $supportPersonRepo;
    private $evaluationGroupRepo;
    private $evaluationPersonRepo;
    private $noteRepo;
    private $documentRepo;

    /** @var SupportGroup|null */
    private $supportGroup = null;
    /** @var EvaluationGroup|null */
    private $evaluationGroup = null;

    public function __construct(
        EntityManagerInterface $em,
        SupportGroupRepository $supportGroupRepo,
        SupportPersonRepository $supportPersonRepo,
        EvaluationGroupRepository $evaluationGroupRepo,
        EvaluationPersonRepository $evaluationPersonRepo,
        NoteRepository $noteRepo,
        DocumentRepository $documentRepo
    ) {
        $this->em = $em;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->supportPersonRepo = $supportPersonRepo;
        $this->evaluationGroupRepo = $evaluationGroupRepo;
        $this->evaluationPersonRepo = $evaluationPersonRepo;
        $this->noteRepo = $noteRepo;
        $this->documentRepo = $documentRepo;
    }

    public function duplicate(SupportGroup $supportGroup)
    {
        $this->supportGroup = $supportGroup;
        if ($this->duplicateSupportGroup($supportGroup)) {
            return $supportGroup;
        }
        if ($this->duplicateSupportPeople($supportGroup)) {
            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if ($this->evaluationGroup && 0 === $supportPerson->getEvaluationsPerson()->count()) {
                    $evaluationPerson = (new EvaluationPerson())
                        ->setEvaluationGroup($supportGroup->getEvaluationsGroup()->last())
                        ->setSupportPerson($supportPerson);

                    $this->em->persist($evaluationPerson);
                }
            }
            $this->em->flush();

            return $this->evaluationGroup;
        }

        return null;
    }

    /**
     * Crée une copie d'un suivi social.
     */
    public function duplicateSupportGroup(SupportGroup $supportGroup): ?SupportGroup
    {
        $lastSupportGroup = $this->supportGroupRepo->findLastSupport($supportGroup);

        if (null === $lastSupportGroup) {
            return null;
        }

        $this->duplicateEvaluation($supportGroup, $lastSupportGroup);
        $this->duplicateDocuments($supportGroup, $lastSupportGroup);
        $this->duplicateNote($supportGroup, $lastSupportGroup);

        $this->em->flush();

        return $supportGroup;
    }

    private function duplicateEvaluation(SupportGroup $newSupportGroup, SupportGroup $lastSupportGroup): void
    {
        $lastEvaluation = $this->evaluationGroupRepo->findEvaluationOfSupport($lastSupportGroup->getId());

        if ($lastEvaluation && 0 === $newSupportGroup->getEvaluationsGroup()->count()) {
            $evaluationGroup = (clone $lastEvaluation)->setSupportGroup($newSupportGroup);
            $newSupportGroup->getEvaluationsGroup()->add($evaluationGroup);
            // Change the supportPerson in every evaluationPerson
            foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
                foreach ($newSupportGroup->getSupportPeople() as $newSupportPerson) {
                    if ($evaluationPerson->getSupportPerson()->getPerson()->getId() === $newSupportPerson->getPerson()->getId()) {
                        $evaluationPerson->setSupportPerson($newSupportPerson);
                    }
                }
            }
        }
    }

    private function duplicateDocuments(SupportGroup $supportGroup, SupportGroup $lastSupportGroup): void
    {
        $documentsOfSupport = $this->documentRepo->findBy(['supportGroup' => $supportGroup]);
        $documentsOfLastSupport = $this->documentRepo->findBy(['supportGroup' => $lastSupportGroup]);

        foreach ($documentsOfLastSupport as $document) {
            $newDocument = (clone $document)->setSupportGroup($supportGroup);
            if (!$this->documentExists($newDocument, $documentsOfSupport)) {
                $supportGroup->getDocuments()->add($newDocument);
            }
        }
    }

    /**
     * Check if the new document exists already in the support.
     *
     * @param Collection<Document>|Document[] $documents
     */
    private function documentExists(Document $newDocument, ?array $documents): bool
    {
        foreach ($documents as $document) {
            if ($newDocument->getInternalFileName() === $document->getInternalFileName()) {
                return true;
            }
        }

        return false;
    }

    private function duplicateNote(SupportGroup $supportGroup, SupportGroup $lastSupportGroup): void
    {
        $lastNote = $this->noteRepo->findOneBy(['supportGroup' => $lastSupportGroup], ['updatedAt' => 'DESC']);

        if ($lastNote) {
            $note = (clone $lastNote)->setSupportGroup($supportGroup);
            $supportGroup->getNotes()->add($note);
        }
    }

    private function duplicateSupportPeople(SupportGroup $supportGroup): SupportGroup
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $lastSupportPerson = $this->supportPersonRepo->findLastSupport($supportPerson);

            if (null === $lastSupportPerson) {
                continue;
            }

            $lastEvaluationPerson = $this->evaluationPersonRepo->findEvaluationOfSupportPerson($lastSupportPerson->getId());

            if ($lastEvaluationPerson && 0 === $supportGroup->getEvaluationsGroup()->count() && null === $this->evaluationGroup) {
                $this->evaluationGroup = $this->cloneEvaluationGroup($lastEvaluationPerson->getEvaluationGroup());
            } elseif ($supportGroup->getEvaluationsGroup()->count()) {
                $this->evaluationGroup = $supportGroup->getEvaluationsGroup()->last();
            }

            if ($lastEvaluationPerson && 0 === $supportPerson->getEvaluationsPerson()->count()) {
                $evaluationPerson = (clone $lastEvaluationPerson)->setSupportPerson($supportPerson);
                $supportPerson->getEvaluationsPerson()->add($evaluationPerson);
                $evaluationPerson->setEvaluationGroup($this->evaluationGroup);

                // if ($lastEvaluationPerson->getInitEvalPerson()) {
                //     $intiEvalPerson = clone $lastEvaluationPerson->getInitEvalPerson();
                //     $intiEvalPerson->setSupportPerson($supportPerson);
                //     $evaluationPerson->setInitEvalPerson($intiEvalPerson);
                // }
            }
        }

        if ($this->evaluationGroup) {
            $this->evaluationGroup->setSupportGroup($this->supportGroup);
            $supportGroup->addEvaluationGroup($this->evaluationGroup);
        }
        // dd($this->evaluationGroup);

        return $supportGroup;
    }

    /**
     * Clone the evaluation of the group.
     */
    private function cloneEvaluationGroup(EvaluationGroup $evaluationGroup): EvaluationGroup
    {
        $newEvaluationGroup = new EvaluationGroup();
        $newEvaluationGroup->setDate(new \DateTime())
            ->setInitEvalGroup(new InitEvalGroup());

        // if ($evaluationGroup->getInitEvalGroup()) {
        //     $newEvaluationGroup->setInitEvalGroup(clone $evaluationGroup->getInitEvalGroup());
        // }
        if ($evaluationGroup->getEvalBudgetGroup()) {
            $newEvaluationGroup->setEvalBudgetGroup(clone $evaluationGroup->getEvalBudgetGroup());
        }
        if ($evaluationGroup->getEvalFamilyGroup()) {
            $newEvaluationGroup->setEvalFamilyGroup(clone $evaluationGroup->getEvalFamilyGroup());
        }
        if ($evaluationGroup->getEvalHousingGroup()) {
            $newEvaluationGroup->setEvalHousingGroup(clone $evaluationGroup->getEvalHousingGroup());
        }
        if ($evaluationGroup->getEvalSocialGroup()) {
            $newEvaluationGroup->setEvalSocialGroup(clone $evaluationGroup->getEvalSocialGroup());
        }
        if (Service::SERVICE_TYPE_HOTEL && $this->supportGroup->getService()->getType() && $evaluationGroup->getEvalHotelLifeGroup()) {
            $newEvaluationGroup->setEvalHotelLifeGroup(clone $evaluationGroup->getEvalHotelLifeGroup());
        }

        return $newEvaluationGroup;
    }
}
