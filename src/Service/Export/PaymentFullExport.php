<?php

namespace App\Service\Export;

use App\Entity\Support\Payment;
use App\Service\ExportExcel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentFullExport extends ExportExcel
{
    protected $router;

    public function __construct(?UrlGeneratorInterface $router)
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

        $this->createSheet('export_paiements', 'xlsx', $arrayData, 16);
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
            'N° suivi' => $payment->getSupportGroup()->getId(),
            'Nom' => $person ? $person->getLastname() : null,
            'Prénom' => $person ? $person->getFirstname() : null,
            'Date de naissance' => $person ? $this->formatDate($person->getBirthdate()) : null,
            'Date d\'arrivée' => $supportGroup->getStartDate() ? $this->formatDate($supportGroup->getStartDate()) : null,
            'Service' => $supportGroup->getService()->getName(),
            'Pôle' => $supportGroup->getService()->getPole()->getName(),
            'Type d\'opération' => $payment->getTypeToString(),
            'PF - Date début de la période' => $this->formatDate($payment->getStartDate()),
            'PF - Date fin de la période' => $this->formatDate($payment->getEndDate()),
            'PF - Montant ressources' => $payment->getResourcesAmt(),
            'Montant loyer' => $payment->getRentAmt(),
            'Montant APL' => $payment->getAplAmt(),
            'Montant à régler' => $payment->getToPayAmt(),
            'Montant réglé' => $payment->getPaidAmt(),
            'Montant restant dû' => $payment->getStillToPayAmt(),
            'Date de l\'opération' => $this->formatDate($payment->getPaymentDate()),
            'Mode de règlement' => $payment->getPaymentType() ? $payment->getPaymentTypeToString() : null,
            'Caution - Montant restitué' => $payment->getReturnAmt(),
            'Commentaire' => $payment->getComment(),
            'Date de création' => $this->formatDate($payment->getCreatedAt()),
            'Créé par' => $payment->getCreatedBy()->getFullname(),
            'Date de modification' => $this->formatDate($payment->getUpdatedAt()),
            'Modifié par' => $payment->getUpdatedBy()->getFullname(),
        ];
    }
}
