<?php

namespace App\EventDispatcher\Support;

use App\Event\Evaluation\EvaluationPersonExportEvent;
use App\Form\Admin\ExportSearchType;
use App\Form\Model\Admin\ExportSearch;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Export\EvaluationPersonExport;
use App\Service\Export\ExportManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;

class EvaluationPersonExporterSubscriber implements EventSubscriberInterface
{
    private $formFactory;
    private $supportPersonRepo;
    private $evaluationPersonExport;
    private $exportManager;

    public function __construct(
        FormFactoryInterface $formFactory,
        SupportPersonRepository $supportPersonRepo,
        EvaluationPersonExport $evaluationPersonExport,
        ExportManager $exportManager
    ) {
        $this->formFactory = $formFactory;
        $this->supportPersonRepo = $supportPersonRepo;
        $this->evaluationPersonExport = $evaluationPersonExport;
        $this->exportManager = $exportManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'evaluation_person.export' => 'exportEvaluationPeople',
        ];
    }

    public function exportEvaluationPeople(EvaluationPersonExportEvent $event): void
    {
        $request = $event->getRequest();

        $form = $this->formFactory->create(ExportSearchType::class, $search = new ExportSearch())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nbSupports = $this->supportPersonRepo->countSupportsToExport($search);

            if ($this->supportPersonRepo->countSupportsToExport($search) > SupportPersonRepository::EXPORT_LIMIT) {
                exit;
            }

            $export = $this->exportManager->create($nbSupports, $search);

            if ($export) {
                $supports = $this->supportPersonRepo->findSupportsFullToExport($search);
                $file = $this->evaluationPersonExport->exportData($supports, $search);

                $this->exportManager->updateAndSend($export, $file);
            }
        }
    }
}
