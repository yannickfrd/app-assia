<?php

namespace App\Service\Export;

use App\Entity\Event\Rdv;
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

        return [
            'N° RDV' => $rdv->getId(),
            'N° suivi' => $supportGroup ? $supportGroup->getId() : null,
            'Titre' => $rdv->getTitle(),
            'Date de début' => $this->formatDatetime($rdv->getStart()),
            'Date de fin' => $this->formatDatetime($rdv->getEnd()),
            'Statut' => $rdv->getStatusToString(),
            'Lieu' => $rdv->getLocation(),
            'Étiquette(s)' => $rdv->getTagsToString(),
            'Intervenant·e·s' => $rdv->getUsersToString(),
            'Suivi' => $supportGroup ? $supportGroup->getHeader()->getFullname() : null,
            'Service' => $supportGroup ? $supportGroup->getService()->getName() : null,
            'Dispositif' => $supportGroup ? $supportGroup->getDevice()->getName() : null,
            'Créé le (date)' => $this->formatDate($rdv->getCreatedAt()),
            'Créé par' => $rdv->getCreatedBy() ? $rdv->getCreatedBy()->getFullname() : 'Auto.',
            'Modifié le (date)' => $this->formatDate($rdv->getUpdatedAt()),
            'Modifié par' => $rdv->getUpdatedBy() ? $rdv->getUpdatedBy()->getFullname() : '',
        ];
    }
}
