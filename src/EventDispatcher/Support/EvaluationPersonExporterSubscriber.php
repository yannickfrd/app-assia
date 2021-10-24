<?php

namespace App\EventDispatcher\Support;

use App\Event\Evaluation\EvaluationPersonExportEvent;
use App\Form\Admin\ExportSearchType;
use App\Form\Model\Admin\ExportSearch;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Export\EvaluationPersonExport;
use App\Service\Export\ExportPersister;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;

class EvaluationPersonExporterSubscriber implements EventSubscriberInterface
{
    private $formFactory;
    private $supportPersonRepo;
    private $evaluationPersonExport;
    private $exportPersister;

    public function __construct(
        FormFactoryInterface $formFactory,
        SupportPersonRepository $supportPersonRepo,
        EvaluationPersonExport $evaluationPersonExport,
        ExportPersister $exportPersister
    ) {
        $this->formFactory = $formFactory;
        $this->supportPersonRepo = $supportPersonRepo;
        $this->evaluationPersonExport = $evaluationPersonExport;
        $this->exportPersister = $exportPersister;
    }

    public static function getSubscribedEvents()
    {
        return [
            'evaluation_person.export' => 'exportEvaluationPeople',
        ];
    }

    public function exportEvaluationPeople(EvaluationPersonExportEvent $event)
    {
        set_time_limit(60 * 60);

        $request = $event->getRequest();

        $form = $this->formFactory->create(ExportSearchType::class, $search = new ExportSearch())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supports = $this->supportPersonRepo->findSupportsFullToExport($search);
            $file = $this->evaluationPersonExport->exportData($supports, $search);

            $this->exportPersister->save($file, $supports, $search);
        }
    }
}
