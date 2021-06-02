<?php

namespace App\EventDispatcher\Support;

use App\Event\Evaluation\EvaluationPersonExportEvent;
use App\Form\Admin\ExportSearchType;
use App\Form\Model\Admin\ExportSearch;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Export\EvaluationPersonExport;
use App\Service\Export\ExportPersister;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;

class EvaluationPersonExporterSubscriber implements EventSubscriberInterface
{
    private $formFactory;
    private $supportPersonRepo;
    private $evaluationPersonExport;
    private $exportPersister;
    private $logger;

    public function __construct(
        FormFactoryInterface $formFactory,
        SupportPersonRepository $supportPersonRepo,
        EvaluationPersonExport $evaluationPersonExport,
        ExportPersister $exportPersister,
        LoggerInterface $logger
    ) {
        $this->formFactory = $formFactory;
        $this->supportPersonRepo = $supportPersonRepo;
        $this->evaluationPersonExport = $evaluationPersonExport;
        $this->exportPersister = $exportPersister;
        $this->logger = $logger;
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

        $this->logger->info('Used memory : '.number_format(memory_get_usage(), 0, ',', ' '));

        $form = $this->formFactory->create(ExportSearchType::class, $search = new ExportSearch())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info('Used memory : '.number_format(memory_get_usage(), 0, ',', ' '));

            $supports = $this->supportPersonRepo->findSupportsFullToExport($search);
            $file = $this->evaluationPersonExport->exportData($supports, $search);

            $this->logger->info('Used memory : '.number_format(memory_get_usage(), 0, ',', ' '));

            $this->exportPersister->save($file, $supports, $search);

            $this->logger->info('Used memory : '.number_format(memory_get_usage(), 0, ',', ' '));
        }
    }
}
