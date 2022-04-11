<?php

declare(strict_types=1);

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Event\Rdv;
use App\Entity\Event\Task;
use App\Entity\Support\Avdl;
use App\Entity\Support\Document;
use App\Entity\Support\HotelSupport;
use App\Entity\Support\Note;
use App\Entity\Support\Payment;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Event\RdvRepository;
use App\Repository\Event\TaskRepository;
use App\Repository\Support\AvdlRepository;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\HotelSupportRepository;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\PaymentRepository;
use App\Repository\Support\PlaceGroupRepository;

class SupportRestorer
{
    private NoteRepository $noteRepo;
    private RdvRepository $rdvRepo;
    private TaskRepository $taskRepo;
    private AvdlRepository $avdlRepo;
    private PaymentRepository $paymentRepo;
    private DocumentRepository $documentRepo;
    private PlaceGroupRepository $placeGroupRepo;
    private HotelSupportRepository $hotelSupportRepo;
    private EvaluationGroupRepository $evaluationGroupRepo;

    public function __construct(
        NoteRepository $noteRepo,
        RdvRepository $rdvRepo,
        TaskRepository $taskRepo,
        AvdlRepository $avdlRepo,
        PaymentRepository $paymentRepo,
        DocumentRepository $documentRepo,
        PlaceGroupRepository $placeGroupRepo,
        HotelSupportRepository $hotelSupportRepo,
        EvaluationGroupRepository $evaluationGroupRepo
    ) {
        $this->noteRepo = $noteRepo;
        $this->rdvRepo = $rdvRepo;
        $this->taskRepo = $taskRepo;
        $this->avdlRepo = $avdlRepo;
        $this->paymentRepo = $paymentRepo;
        $this->documentRepo = $documentRepo;
        $this->placeGroupRepo = $placeGroupRepo;
        $this->hotelSupportRepo = $hotelSupportRepo;
        $this->evaluationGroupRepo = $evaluationGroupRepo;
    }

    public function restore(SupportGroup $support): void
    {
        $supportId = $support->getId();
        $supportDeletedAt = $support->getDeletedAt();

        $entitiesElement = $this->getElementsBeforeRestore($supportId);
        foreach ($entitiesElement as $elements) {
            foreach ($elements as $element) {
                if ($element->getDeletedAt() == $supportDeletedAt) {
                $element->setDeletedAt(null);
                }
            }
        }

        foreach ($support->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getDeletedAt() == $supportDeletedAt) {
                $supportPerson->setDeletedAt(null);
            }
        }

        $support->setDeletedAt(null);
    }

    private function getElementsBeforeRestore(int $supportId): array
    {
        return [
            $this->noteRepo->findNotesOfSupportDeleted($supportId),
            $this->rdvRepo->findRdvOfSupportDeleted($supportId),
            $this->taskRepo->findTaskOfSupportDeleted($supportId),
            $this->avdlRepo->findAvdlOfSupportDeleted($supportId),
            $this->paymentRepo->findRdvOfSupportDeleted($supportId),
            $this->documentRepo->findDocumentOfSupportDeleted($supportId),
            $this->hotelSupportRepo->findHoteOfSupportDeleted($supportId),
            $this->placeGroupRepo->findPlaceGroupOfSupportDeleted($supportId),
            $this->evaluationGroupRepo->findEvaluationOfSupportDeleted($supportId),
        ];
    }

}