<?php

namespace App\Service\Export;

use App\Entity\Support\Payment;
use App\Service\ExportExcel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentAccountingExport extends ExportExcel
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

        $this->createSheet('export_paiements_compta', 'xlsx', $arrayData, 22);
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

        return [
            'N° opération' => $payment->getId(),
            'Nom' => $person ? $person->getFullname() : null,
            'Service' => $supportGroup->getService()->getName(),
            'Type d\'opération' => $payment->getTypeToString(),
            'PF - Date début de la période' => $this->formatDate($payment->getStartDate()),
            'PF - Date fin de la période' => $this->formatDate($payment->getEndDate()),
            'Montant à régler' => $payment->getToPayAmt(),
            'Montant réglé' => $payment->getPaidAmt(),
            'Montant restant dû' => $payment->getStillToPayAmt(),
            'Date de l\'opération' => $this->formatDate($payment->getPaymentDate()),
            'Mode de règlement' => $payment->getPaymentType() ? $payment->getPaymentTypeToString() : null,
            'Commentaire' => $payment->getComment(),
            'Travailleur social' => $payment->getCreatedBy()->getFullname(),
        ];
    }
}
