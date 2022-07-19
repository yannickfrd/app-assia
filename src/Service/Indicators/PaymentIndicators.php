<?php

namespace App\Service\Indicators;

use App\Entity\Support\Payment;
use App\Form\Model\Support\PaymentSearch;
use App\Service\Calendar;

class PaymentIndicators
{
    protected $nbPayments = 0;
    protected $sumToPayAmt = 0;
    protected $sumPaidAmt = 0;
    protected $sumStillToPayAmt = 0;

    public function __construct()
    {
    }

    /**
     * @param Payment[] $payments
     */
    public function getIndicators(array $payments, PaymentSearch $search): array
    {
        switch ($search->getDateType()) {
            case 1:
                $date = 'getPaymentDate';
                break;
            case 2:
                $date = 'getStartDate';
                break;
            default:
                $date = 'getCreatedAt';
                break;
        }

        $start = $search->getStart() ?? new \DateTime('2019-01-01');
        $end = $search->getEnd();
        $months = [];
        $months[] = $start;
        $month = clone $start;
        $end = new \DateTime($end->format('Y-m').'-01');
        $nbMonths = ($start->diff($end)->y * 12) + $start->diff($end)->m + round($start->diff($end)->d / (365 / 12));

        for ($i = 0; $i < $nbMonths; ++$i) {
            $month = (new \DateTime($month->format('Y-m-d')))->modify('+1 month');
            $months[] = $month;
        }

        $datasMonths = [];

        foreach ($months as $month) {
            $nbPayments = 0;
            $sumToPayAmt = 0;
            $sumPaidAmt = 0;
            $sumStillToPayAmt = 0;
            foreach ($payments as $payment) {
                if ($this->withinMonth($month, $payment->{$date}())) {
                    ++$nbPayments;
                    $sumToPayAmt += $payment->getToPayAmt();
                    $sumPaidAmt += $payment->getPaidAmt();
                    $sumStillToPayAmt += $payment->getStillToPayAmt();
                }
            }

            $datasMonths[$month->format('Y-m')] = [
                'date' => $month,
                'monthToString' => Calendar::MONTHS[(int) $month->format('m')].' '.$month->format('Y'),
                'nbPayments' => $nbPayments,
                'sumToPayAmt' => $sumToPayAmt,
                'averagePaymentAmt' => $nbPayments ? round(($sumToPayAmt / $nbPayments) * 100) / 100 : null,
                'sumPaidAmt' => $sumPaidAmt,
                'averagePaidAmt' => $nbPayments ? round(($sumPaidAmt / $nbPayments) * 100) / 100 : null,
                'sumStillToPayAmt' => $sumStillToPayAmt,
            ];

            $this->nbPayments += $nbPayments;
            $this->sumToPayAmt += $sumToPayAmt;
            $this->sumPaidAmt += $sumPaidAmt;
            $this->sumStillToPayAmt += $sumStillToPayAmt;
        }

        return [
            'months' => $datasMonths,
            'nbPayments' => $this->nbPayments,
            'sumToPayAmt' => $this->sumToPayAmt,
            'averagePaymentAmt' => $this->nbPayments ? round(($this->sumToPayAmt / $this->nbPayments) * 100) / 100 : null,
            'sumPaidAmt' => $this->sumPaidAmt,
            'averagePaidAmt' => $this->nbPayments ? round(($this->sumPaidAmt / $this->nbPayments) * 100) / 100 : null,
            'sumStillToPayAmt' => $this->sumStillToPayAmt,
        ];
    }

    /**
     * Retourne si la participation financière est à l'intérieur du mois.
     */
    public function withinMonth(\DateTime $month, ?\DateTime $date = null): bool
    {
        return $date ? $date->format('Y-m') === $month->format('Y-m') : false;
    }
}
