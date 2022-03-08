<?php

namespace App\Service\Export;

use App\Entity\Event\Task;
use App\Service\ExportExcel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TaskExport extends ExportExcel
{
    protected $router;

    public function __construct(UrlGeneratorInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * Exporte les données.
     */
    public function exportData($tasks)
    {
        $arrayData[] = array_keys($this->getDatas($tasks[0]));

        foreach ($tasks as $task) {
            $arrayData[] = $this->getDatas($task);
        }

        $this->createSheet($arrayData, [
            'name' => 'export_tâches',
            'columnsWidth' => 16,
            'totalRow' => true,
        ]);

        return $this->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(Task $task): array
    {
        $supportGroup = $task->getSupportGroup();

        return [
            'N° tâche' => $task->getId(),
            'N° suivi' => $supportGroup ? $supportGroup->getId() : null,
            'Nom de la tâche' => $task->getTitle(),
            'Date d\'échéance' => $this->formatDatetime($task->getEnd()),
            'Statut' => $task->getStatusToString(),
            'Priorité' => $task->getLevelToString(),
            'Professionnel·le(s)' => $task->getUsersToString(),
            'Suivi' => $supportGroup ? $supportGroup->getHeader()->getFullname() : null,
            'Étiquette(s)' => $task->getTagsToString(),
            'Service' => $supportGroup ? $supportGroup->getService()->getName() : null,
            'Dispositif' => $supportGroup ? $supportGroup->getDevice()->getName() : null,
            'Pôle' => $supportGroup ? $supportGroup->getService()->getPole()->getName() : null,
            'Créé le (date)' => $this->formatDate($task->getCreatedAt()),
            'Créé par' => $task->getCreatedBy() ? $task->getCreatedBy()->getFullname() : 'Auto.',
            'Modifié le (date)' => $this->formatDate($task->getUpdatedAt()),
            'Modifié par' => $task->getUpdatedBy() ? $task->getUpdatedBy()->getFullname() : '',
        ];
    }
}
