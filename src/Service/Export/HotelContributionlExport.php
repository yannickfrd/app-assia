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
     */
    public function exportData($payments)
    {
        $arrayData[] = array_keys($this->getDatas($payments[0]));

        foreach ($payments as $payment) {
            $arrayData[] = $this->getDatas($payment);
        }

        $this->createSheet('export_paf_hotel', 'xlsx', $arrayData, 22);
        $this->addTotalRow();

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
            'ID PAF (interne)' => $payment->getId(),
            'ID groupe SI-SIAO' => $supportGroup->getPeopleGroup()->getSiSiaoId(),
            'Nom ménage' => $person->getLastname(),
            'Prénom' => $person->getFirstname(),
            'Date de naissance' => $this->formatDate($person->getBirthdate()),
            'Montant PAF' => (string) $payment->getToPayAmt(),
            'PAF à zéro' => $payment->getNoContribToString(),
            'SIAO prescripteur nuitée' => $organization ? $organization->getName() : null,
            'PASH' => 'PASH 95',
            'Date début PAF' => $this->formatDate($payment->getStartDate()),
            'Date fin PAF' => $this->formatDate($payment->getEndDate()),
        ];
    }
}
