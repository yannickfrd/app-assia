<?php

namespace App\Service\Indicators;

use App\Service\Calendar;

class ContributionIndicators
{
    protected $nbContributions = 0;
    protected $sumDueAmt = 0;
    protected $sumPaidAmt = 0;
    protected $sumStillDueAmt = 0;

    public function __construct()
    {
    }

    public function getIndicators(array $contributions, \DateTime $start, \DateTime $end)
    {
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
            $sumDueAmt = 0;
            $sumPaidAmt = 0;
            $sumStillDueAmt = 0;
            foreach ($contributions as $contribution) {
                if ($this->withinMonth($contribution->getDate(), $month)) {
                    ++$nbContributions;
                    $sumDueAmt += $contribution->getDueAmt();
                    $sumPaidAmt += $contribution->getPaidAmt();
                    $sumStillDueAmt += $contribution->getStillDueAmt();
                }
            }

            $datasMonths[$month->format('Y-m')] = [
                'date' => $month,
                'monthToString' => Calendar::MONTHS[(int) $month->format('m')].' '.$month->format('Y'),
                'nbContributions' => $nbContributions,
                'sumDueAmt' => $sumDueAmt,
                'averageContributionAmt' => $nbContributions ? round(($sumDueAmt / $nbContributions) * 100) / 100 : '',
                'sumPaidAmt' => $sumPaidAmt,
                'averagePaidAmt' => $nbContributions ? round(($sumPaidAmt / $nbContributions) * 100) / 100 : '',
                'sumStillDueAmt' => $sumStillDueAmt,
            ];

            $this->nbContributions += $nbContributions;
            $this->sumDueAmt += $sumDueAmt;
            $this->sumPaidAmt += $sumPaidAmt;
            $this->sumStillDueAmt += $sumStillDueAmt;
        }

        return [
            'months' => $datasMonths,
            'nbContributions' => $this->nbContributions,
            'sumDueAmt' => $this->sumDueAmt,
            'averageContributionAmt' => $this->nbContributions ? round(($this->sumDueAmt / $this->nbContributions) * 100) / 100 : '',
            'sumPaidAmt' => $this->sumPaidAmt,
            'averagePaidAmt' => $this->nbContributions ? round(($this->sumPaidAmt / $this->nbContributions) * 100) / 100 : '',
            'sumStillDueAmt' => $this->sumStillDueAmt,
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
