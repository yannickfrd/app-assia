<?php

namespace App\EventDispatcher\Support;

use App\EntityManager\ExportManager;
use App\Form\Model\Admin\ExportSearch;
use App\Event\Support\SupportPersonExportEvent;
use App\Service\Export\SupportPersonFullExport;
use App\Repository\Support\SupportPersonRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SupportPersonFullExportSubscriber implements EventSubscriberInterface
{
    private $repoSupportPerson;
    private $supportPersonFullExport;
    private $exportManager;

    public function __construct(
        SupportPersonRepository $repoSupportPerson,
        SupportPersonFullExport $supportPersonFullExport,
        ExportManager $exportManager
    ) {
        $this->repoSupportPerson = $repoSupportPerson;
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

        /** @var ExportSearch $search */
        $search = $event->getSearch();

        $supports = $this->repoSupportPerson->findSupportsFullToExport($search);

        $file = $this->supportPersonFullExport->exportData($supports);

        $this->exportManager->save($file, $supports, $search);
    }
}
