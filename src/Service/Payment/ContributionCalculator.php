<?php

namespace App\Service\Payment;

use App\Entity\Evaluation\EvalBudgetCharge;
use App\Entity\Evaluation\EvalBudgetDebt;
use App\Entity\Evaluation\EvalBudgetResource;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\Resource;
use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\PlaceRepository;
use Doctrine\Common\Collections\Collection;

class ContributionCalculator
{
    public const AGE_ADULT = 18;
    public const AGE_TEENAGE = 14;

    /** @var int Unité de consommation (UC) pour le 1er adulte */
    public const UC_FIRST_ADULT = 1;
    /** @var float Unité de consommation (UC) pour les autres personnes de 14 ans ou plus */
    public const UC_OTHER_ADULT_OR_TEENAGE = 0.5;
    /** @var float Unité de consommation (UC) pour un enfant (moins de 14 ans) */
    public const UC_CHILD = 0.3;

    /** @var int Reste à vivre journalier unitaire minimum (12 euros) */
    public const MIN_REST_TO_LIVE = 12;

    protected $evaluationRepo;
    protected $placeRepo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Service */
    protected $service;

    /** @var Device */
    protected $device;

    /** @var EvaluationGroup */
    protected $evaluationGroup;

    /** @var Payment */
    protected $payment;

    /** @var float Pourcentage appliqué sur le montant total des ressources prises en compte, déduction faite des charges */
    protected $contributionRate;

    /** @var int Nombre de jours de participation financière */
    protected $nbDaysContribution;

    public function __construct(EvaluationGroupRepository $evaluationRepo, PlaceRepository $placeRepo)
    {
        $this->evaluationRepo = $evaluationRepo;
        $this->placeRepo = $placeRepo;
    }

    public function calculate(SupportGroup $supportGroup, ?Payment $payment): Payment
    {
        $this->supportGroup = $supportGroup;
        $this->service = $supportGroup->getService();
        $this->device = $supportGroup->getDevice();
        $this->payment = $payment ?? new Payment();

        if (Device::RENT_CONTRIBUTION === $this->device->getContributionType()) {
            return $this->getRent();
        }

        $this->contributionRate = $this->getContributionRate();

        if (Service::SERVICE_TYPE_HOTEL === $this->service->getType()) {
            return $this->getContributionHotel();
        }

        return $this->getContributionHeb();
    }

    protected function getContributionHeb(): Payment
    {
        $ratioNbDays = $this->getRatioNbDays();

        /** @var float Nombre d'unité de consommation */
        $nbConsumUnits = $this->getNbConsumUnits();

        /** @var float Budget du ménage (ressources - charges) */
        $budgetBalanceAmt = $this->getBudgetBalanceAmt();

        /** @var float Montant de la participation financière */
        $paymentAmt = round($budgetBalanceAmt * $this->contributionRate * $ratioNbDays, 0, PHP_ROUND_HALF_DOWN);

        /** @var float Reste à vivre journalier par personne */
        $restToLive = round(($budgetBalanceAmt - $paymentAmt) / $nbConsumUnits / $this->nbDaysContribution, 2);

        return $this->payment
            ->setContributionRate($this->contributionRate)
            ->setRatioNbDays($ratioNbDays)
            ->setNbConsumUnits($nbConsumUnits)
            ->setRestToLive($restToLive)
            ->setToPayAmt($paymentAmt);
    }

    protected function getRent(): Payment
    {
        $place = $this->placeRepo->findCurrentPlaceOfSupport($this->supportGroup);
        $rentAmt = $this->payment->getRentAmt() ?? $place->getRentAmt();
        $ratioNbDays = $this->getRatioNbDays();
        $this->getBudgetBalanceAmt();

        $toPayAmt = round(($rentAmt * $ratioNbDays) - $this->payment->getAplAmt(), 2, PHP_ROUND_HALF_DOWN);

        return $this->payment
            ->setRentAmt($rentAmt)
            ->setRatioNbDays($ratioNbDays)
            ->setTheoricalContribAmt($rentAmt)
            ->setToPayAmt($toPayAmt);
    }

    protected function getContributionHotel(): Payment
    {
        $this->getRatioNbDays();

        /** @var float Nombre d'unité de consommation */
        $nbConsumUnits = $this->getNbConsumUnits();

        /** @var float Budget du ménage (ressources - charges) */
        $budgetBalanceAmt = $this->getBudgetBalanceAmt(HotelContributionCalculator::RESOURCES, HotelContributionCalculator::CHARGES);

        /** @var float Montant de la participation financière */
        $theoricalContribAmt = round($budgetBalanceAmt * $this->contributionRate, 0, PHP_ROUND_HALF_DOWN);

        /** @var float Reste à vivre journalier unitaire */
        $restToLive = round(($budgetBalanceAmt - $theoricalContribAmt) / $nbConsumUnits / HotelContributionCalculator::NB_DAYS, 2);

        $toPayAmt = $theoricalContribAmt;

        if ($restToLive < ($this->service->getMinRestToLive() ?? self::MIN_REST_TO_LIVE)
            || true === $this->payment->getNoContrib()) {
            $toPayAmt = 0;
        }

        return $this->payment
            ->setContributionRate($this->contributionRate)
            ->setNbConsumUnits($nbConsumUnits)
            ->setTheoricalContribAmt($theoricalContribAmt)
            ->setRestToLive($restToLive)
            ->setToPayAmt($toPayAmt);
    }

    /**
     * Get sum of consumption unit.
     */
    protected function getNbConsumUnits(): float
    {
        /** @var float */
        $nbConsumUnits = 0;
        /** @var bool */
        $firstAdult = true;

        foreach ($this->supportGroup->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getEndDate() != $this->supportGroup->getEndDate()) {
                continue;
            }

            $age = $supportPerson->getPerson()->getBirthdate()->diff($this->payment->getEndDate())->y;

            if ($age >= self::AGE_ADULT && true === $firstAdult) {
                $nbConsumUnits += self::UC_FIRST_ADULT;
                $firstAdult = false;
            } elseif ($age >= self::AGE_TEENAGE && false === $firstAdult) {
                $nbConsumUnits += self::UC_OTHER_ADULT_OR_TEENAGE;
            } else {
                $nbConsumUnits += self::UC_CHILD;
            }
        }

        return round($nbConsumUnits > 0 ? $nbConsumUnits : 1, 2);
    }

    protected function getBudgetBalanceAmt(
        array $resourceTypes = Resource::RESOURCES,
        array $chargeTypes = EvalBudgetCharge::CHARGES
    ): float {
        $evaluationGroup = $this->evaluationRepo->findEvaluationBudget($this->supportGroup);

        /** @var float Ressources du ménage */
        $resourcesGroupAmt = 0;
        /** @var float Charges du ménage */
        $chargesGroupAmt = 0;

        $endDateSupportGroup = $this->supportGroup->getEndDate();

        if ($evaluationGroup) {
            foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
                $supportPerson = $evaluationPerson->getSupportPerson();

                if (null === $supportPerson || $supportPerson->getEndDate() != $endDateSupportGroup
                    || $supportPerson->getPerson()->getAge() < self::AGE_ADULT) {
                    continue;
                }

                $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();
                if ($evalBudgetPerson) {
                    $resourcesGroupAmt += $this->getSumAmt($resourceTypes, $evalBudgetPerson->getEvalBudgetResources());
                    $chargesGroupAmt += $this->getSumAmt($chargeTypes, $evalBudgetPerson->getEvalBudgetCharges());
                }
            }
        }

        if (null !== $this->payment->getResourcesAmt()) {
            $resourcesGroupAmt = $this->payment->getResourcesAmt();
        } else {
            $this->payment->setResourcesAmt(round($resourcesGroupAmt));
        }

        if (null !== $this->payment->getChargesAmt()) {
            $chargesGroupAmt = $this->payment->getChargesAmt();
        } else {
            $this->payment->setChargesAmt(round($chargesGroupAmt));
        }

        return $resourcesGroupAmt - $chargesGroupAmt;
    }

    /**
     * @param Collection<EvalBudgetResource>|Collection<EvalBudgetCharge>|Collection<EvalBudgetDebt>|null $finances
     */
    protected function getSumAmt(array $values, ?Collection $finances = null): float
    {
        if (!$finances) {
            return 0;
        }

        $sumAmt = 0;

        foreach ($finances as $finance) {
            if (array_key_exists($finance->getType(), $values)) {
                $sumAmt += $finance->getAmount();
            }
        }

        return $sumAmt;
    }

    public function formatPrice(int|float|null $value, int $decimals = 2): string
    {
        return number_format($value ?? 0, $decimals, ',', ' ').' €';
    }

    protected function getContributionRate(): ?float
    {
        if (Service::VAR_PERCENT_CONTRIBUTION === $this->service->getContributionType()) {
            return $this->device ? $this->device->getContributionRate() : null;
        }

        return $this->service->getContributionRate();
    }

    /**
     * Donne le ratio entre le nombre de jours de présence et le nombre de jours dans le mois (prorata).
     */
    protected function getRatioNbDays(): float
    {
        if (!$this->payment->getStartDate()) {
            $this->payment->setStartDate(max(
                (new \DateTime())->modify('first day of this month'),
                $this->supportGroup->getStartDate()
            ));
        }
        if (!$this->payment->getEndDate()) {
            /** @var \Datetime */
            $startDate = $this->payment->getStartDate();
            $endMonthDate = (clone $startDate)->modify('last day of this month');
            $this->payment->setEndDate(min($endMonthDate, $this->supportGroup->getEndDate() ?? $endMonthDate));
            if ($this->payment->getEndDate() < $this->payment->getStartDate()) {
                $this->payment->setEndDate($this->payment->getStartDate());
            }
        }

        /** @var \Datetime Premier jour de participation */
        $startContributionDate = $this->payment->getStartDate();

        $this->nbDaysContribution = $startContributionDate->diff($this->payment->getEndDate())->d + 1;

        /** @var \Datetime Premier jour du mois */
        $startMonthDate = (clone $startContributionDate)->modify('first day of this month');
        /** @var \Datetime Dernier jour du mois */
        $endMonthDate = (clone $startContributionDate)->modify('last day of this month');

        /** @var int Nombre de jours dans le mois */
        $nbDaysInMonth = $startMonthDate->diff($endMonthDate)->d + 1;

        /** @var int Ratio du nombre de jours */
        $ratioNbDays = $this->nbDaysContribution / $nbDaysInMonth;

        return round($ratioNbDays, 4);
    }
}
