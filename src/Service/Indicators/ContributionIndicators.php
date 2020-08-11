<?php

namespace App\Service\Indicators;

use App\Form\Model\ContributionSearch;
use App\Service\Calendar;

class ContributionIndicators
{
    protected $nbContributions = 0;
    protected $sumToPayAmt = 0;
    protected $sumPaidAmt = 0;
    protected $sumStillToPayAmt = 0;

    public function __construct()
    {
    }

    public function getIndicators(array $contributions, ContributionSearch $search)
    {
        switch ($search->getDateType()) {
            case 1:
                $date = 'getPeriodContribution';
                break;
            case 2:
                $date = 'getPaymentDate';
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
        $nbMonths = ($start->diff($end)->y * 12) + $start->diff($end)->m + round(($start->diff($end)->d / (365 / 12)));

        for ($i = 0; $i < $nbMonths; ++$i) {
            $month = (new \DateTime($month->format('Y-m-d')))->modify('+1 month');
            $months[] = $month;
        }

        $datasMonths = [];

        foreach ($months as $month) {
            $nbContributions = 0;
            $sumToPayAmt = 0;
            $sumPaidAmt = 0;
            $sumStillToPayAmt = 0;
            foreach ($contributions as $contribution) {
                if ($this->withinMonth($contribution->{$date}(), $month)) {
                    ++$nbContributions;
                    $sumToPayAmt += $contribution->getToPayAmt();
                    $sumPaidAmt += $contribution->getPaidAmt();
                    $sumStillToPayAmt += $contribution->getStillToPayAmt();
                }
            }

            $datasMonths[$month->format('Y-m')] = [
                'date' => $month,
                'monthToString' => Calendar::MONTHS[(int) $month->format('m')].' '.$month->format('Y'),
                'nbContributions' => $nbContributions,
                'sumToPayAmt' => $sumToPayAmt,
                'averageContributionAmt' => $nbContributions ? round(($sumToPayAmt / $nbContributions) * 100) / 100 : '',
                'sumPaidAmt' => $sumPaidAmt,
                'averagePaidAmt' => $nbContributions ? round(($sumPaidAmt / $nbContributions) * 100) / 100 : '',
                'sumStillToPayAmt' => $sumStillToPayAmt,
            ];

            $this->nbContributions += $nbContributions;
            $this->sumToPayAmt += $sumToPayAmt;
            $this->sumPaidAmt += $sumPaidAmt;
            $this->sumStillToPayAmt += $sumStillToPayAmt;
        }

        return [
            'months' => $datasMonths,
            'nbContributions' => $this->nbContributions,
            'sumToPayAmt' => $this->sumToPayAmt,
            'averageContributionAmt' => $this->nbContributions ? round(($this->sumToPayAmt / $this->nbContributions) * 100) / 100 : '',
            'sumPaidAmt' => $this->sumPaidAmt,
            'averagePaidAmt' => $this->nbContributions ? round(($this->sumPaidAmt / $this->nbContributions) * 100) / 100 : '',
            'sumStillToPayAmt' => $this->sumStillToPayAmt,
        ];
    }

    /**
     * Retourne si la participation financière est à l'intérieur du mois.
     */
    public function withinMonth(\datetime $date, \datetime $month): bool
    {
        return $month->format('Y-m') == $date->format('Y-m');
    }
}
