<?php

namespace App\Service\Export;

use App\Entity\Support\Payment;
use App\Service\ExportExcel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HotelContributionlExport extends ExportExcel
{
    protected $router;

    public function __construct(UrlGeneratorInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * Exporte les données.
     *
     * @return StreamedResponse|Response|string
     */
    public function exportData(array $payments)
    {
        $arrayData[] = array_keys($this->getDatas($payments[0]));

        foreach ($payments as $payment) {
            $arrayData[] = $this->getDatas($payment);
        }

        $this->createSheet($arrayData, [
            'name' => 'export_paf_hotel',
            'columnsWidth' => 22,
            'totalRow' => true,
        ]);

        return $this->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(Payment $payment): array
    {
        $supportGroup = $payment->getSupportGroup();
        $person = $supportGroup->getHeader();
        $originRequest = $supportGroup->getOriginRequest();
        $organization = $originRequest ? $originRequest->getOrganization() : null;

        return [
            'Période concernée' => $payment->getStartDate() ? $payment->getStartDate()->format('m/Y') : null,
            'ID PAF (interne)' => $payment->getId(),
            'ID groupe SI-SIAO' => $supportGroup->getPeopleGroup()->getSiSiaoId(),
            'Nom ménage' => $person ? $person->getLastname() : null,
            'Prénom' => $person ? $person->getFirstname() : null,
            'Date de naissance' => $person ? $this->formatDate($person->getBirthdate()) : null,
            'Montant PAF' => (string) $payment->getToPayAmt(),
            'PAF à zéro' => $payment->getNoContribToString().($payment->getNoContribReason() ? ' ('.$payment->getNoContribReasonToString().')' : ''),
            'SIAO prescripteur nuitée' => $organization ? $organization->getName() : null,
            'PASH' => 'PASH 95',
            'Commentaire' => $payment->getComment(),
        ];
    }
}
