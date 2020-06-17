<?php

namespace App\Export;

use App\Entity\Rdv;
use App\Service\ExportExcel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RdvExport
{
    protected $router;

    public function __construct(UrlGeneratorInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * Exporte les données.
     */
    public function exportData($rdvs)
    {
        $arrayData[] = array_keys($this->getDatas($rdvs[0]));

        foreach ($rdvs as $rdv) {
            $arrayData[] = $this->getDatas($rdv);
        }

        $export = new ExportExcel('export_rdvs', 'xlsx', $arrayData, 16);
        $export->addTotalRow();

        return $export->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(Rdv $rdv): array
    {
        $supportGroup = $rdv->getSupportGroup();
        $person = null;

        if ($supportGroup) {
            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if ($supportPerson->getHead()) {
                    $person = $supportPerson->getPerson();
                }
            }
        }

        return [
            'N° RDV' => $rdv->getId(),
            'N° suivi' => $supportGroup ? $supportGroup->getId() : null,
            'Titre' => $rdv->getTitle(),
            'Date de début' => Date::PHPToExcel($rdv->getStart()->format('Y-m-d H:i')),
            'Date de fin' => Date::PHPToExcel($rdv->getEnd()->format('Y-m-d H:i')),
            'Lieu' => $rdv->getLocation(),
            'Suivi' => $person ? $person->getFullname() : null,
            'Service' => $supportGroup ? $supportGroup->getService()->getName() : null,
            'Pôle' => $supportGroup ? $supportGroup->getService()->getPole()->getName() : null,
            'Date de création' => $this->formatDate($rdv->getCreatedAt()),
            'Créé par' => $rdv->getCreatedBy()->getFullname(),
            'Date de modification' => $this->formatDate($rdv->getUpdatedAt()),
            'Modifié par' => $rdv->getUpdatedBy()->getFullname(),
        ];
    }

    public function formatDate($date)
    {
        return $date ? Date::PHPToExcel($date->format('Y-m-d')) : null;
    }
}
