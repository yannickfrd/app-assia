<?php

namespace App\Service\Export;

use App\Entity\Support\Rdv;
use App\Service\ExportExcel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RdvExport extends ExportExcel
{
    protected $router;

    public function __construct(UrlGeneratorInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * Exporte les données.
     *
     * @param Rdv[] $rdvs
     */
    public function exportData(array $rdvs): Response
    {
        $arrayData[] = array_keys($this->getDatas($rdvs[0]));

        foreach ($rdvs as $rdv) {
            $arrayData[] = $this->getDatas($rdv);
        }

        $this->createSheet($arrayData, [
            'name' => 'export_rdvs',
            'columnsWidth' => 16,
            'totalRow' => true,
        ]);

        return $this->exportFile();
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
            'Date de début' => $this->formatDatetime($rdv->getStart()),
            'Date de fin' => $this->formatDatetime($rdv->getEnd()),
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
}
