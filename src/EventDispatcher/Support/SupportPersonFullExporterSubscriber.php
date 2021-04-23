<?php

namespace App\EventDispatcher\Support;

use App\Service\Export\ExportPersister;
use App\Event\Support\SupportPersonExportEvent;
use App\Form\Admin\ExportSearchType;
use App\Form\Model\Admin\ExportSearch;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Export\SupportPersonFullExport;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;

class SupportPersonFullExporterSubscriber implements EventSubscriberInterface
{
    private $formFactory;
    private $supportPersonRepo;
    private $supportPersonFullExport;
    private $exportManager;

    public function __construct(
        FormFactoryInterface $formFactory,
        SupportPersonRepository $supportPersonRepo,
        SupportPersonFullExport $supportPersonFullExport,
        ExportPersister $exportManager
    ) {
        $this->formFactory = $formFactory;
        $this->supportPersonRepo = $supportPersonRepo;
        $this->supportPersonFullExport = $supportPersonFullExport;
        $this->exportManager = $exportManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'support_person.full_export' => 'exportFullSupportPerson',
        ];
    }

    public function exportFullSupportPerson(SupportPersonExportEvent $event)
    {
        set_time_limit(60 * 60);

        $request = $event->getRequest();

        $form = $this->formFactory->create(ExportSearchType::class, $search = new ExportSearch())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supports = $this->supportPersonRepo->findSupportsFullToExport($search);

            $file = $this->supportPersonFullExport->exportData($supports);

            $this->exportManager->save($file, $supports, $search);
        }
    }
}
