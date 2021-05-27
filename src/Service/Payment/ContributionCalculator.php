<?php

namespace App\Service\Payment;

use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\PlaceRepository;

/* Le taux de participation appliqué sur le montant total des ressources prises en compte, déduction faite des charges.
*   - Le pourcentage est unique quelle que soit la composition du ménage : 10 % des ressources.
*   NB : Les groupes de suivi du référentiel régional de la PAF examineront la détermination d’un montant maximal de PAF mensuel.
*/

/*
* Déterminer le reste à vivre journalier minimal dégagé après le calcul de la PAF
* - Après le calcul de la PAF, un RPV journalier minimal de 12 euros doit être dégagé par unité de consommation (UC).
* - La détermination du nombre d’UC dans le ménage est calculé sur la base de l’échelle OCDE,
*   utilisée par l’INSEE et par les bailleurs sociaux d’Île-de-France en application du référentiel AORIF :
*   1 UC pour le 1er adulte ; 0,5 UC pour les autres personnes de 14 ans ou plus ; 0,3 UC pour les enfants de moins de 14 ans.
* NB :
* - Le montant de RPV journalier minimal est unique, quel que soit le niveau d’équipement des hôtels.
* - Si le RPV journalier minimal n’est pas atteint, aucune PAF n’est appliquée y compris d’un montant symbolique.
*   Pour rappel, dans ces situations, la gestion du budget doit néanmoins être travaillée avec le ménage :
*   en présence de ressources et au regard de la situation, la constitution d’une épargne peut notamment être recherchée.
*/

/* Ressources
* La typologie des ressources prises en compte : La PAF est calculée sur des ressources pérennes renseignées dans le SISIAO
* (notamment salaires, prestations sociales (y compris liées au handicap), bourses scolaires etc).
* NB : La PAF ne prend pas en compte des ressources temporaires (notamment primes ponctuelles, rappels de prestations sociales etc.).
* Récupérer les charges (toutes sauf "Autre")
* La nature des charges à déduire des ressources : Les charges à déduire dont les dettes dont la typologie et le montant sont renseignés dans l’évaluation sociale SISIAO.
* NB : Des charges relatives à l’alimentation et à l’envoi d’argent au pays ne sont pas à déduire et intègrent le reste à vivre (RPV) qui devra être dégagé après le calcul de la PAF.
*/

/* "PAF à 0€"
* - La PAF à 0€ est à distinguer d’une non application de PAF lorsque le ménage n’est pas éligible à la PAF ou lorsque le RPV journalier minimal n’est pas dégagé.
* - L’application d’une PAF à 0€ entraîne l’émission d’un avis d’échéance par EFFICASH.
* - L’application d’une PAF à 0€ constitue un outil éducatif et d’accompagnement social.
*   Il appartient au référent social d’expliquer cette décision au ménage, notamment à l’appui de l’avis d’échéance reçu.
* NB : Les modalités de reporting suivront l’application du nombre de PAF à 0€ émises par PASH et par motif.
*/

class ContributionCalculator
{
    /** @var float Nombre de jours dans le mois (moyenne) */
    public const NB_DAYS = 30;

    public const AGE_ADULT = 18;
    public const AGE_TEENAGE = 14;

    /** @var float Unité de consommation (UC) pour le 1er adulte */
    public const UC_FIRST_ADULT = 1;
    /** @var float Unité de consommation (UC) pour les autres personnes de 14 ans ou plus */
    public const UC_OTHER_ADULT_OR_TEENAGE = 0.5;
    /** @var float Unité de consommation (UC) pour un enfant (moins de 14 ans) */
    public const UC_CHILD = 0.3;

    /** @var float Reste à vivre journalier unitaire minimum (12 euros) */
    public const MIN_REST_TO_LIVE = 12;

    private const RESOURCES_TYPE = [
        'salary' => 'Salaire', // SI-SIAO => OK
        'activityBonus' => 'Prime d\'activité', // SI-SIAO => OK
        'unemplBenefit' => 'ARE', // SI-SIAO => OK
        'minimumIncome' => 'RSA', // SI-SIAO => OK
        'familyAllowance' => 'AF', // SI-SIAO => OK
        'disAdultAllowance' => 'AAH', // SI-SIAO => OK
        'disChildAllowance' => 'AEEH', //SI-SIAO => OK
        'asf' => 'ASF', // ??
        'solidarityAllowance' => 'ASS', // SI-SIAO OK
        'asylumAllowance' => 'ADA', // SI-SIAO => OK
        'tempWaitingAllowance' => 'ATA', // SI-SIAO => OK
        'scholarships' => 'Bourse', // SI-SIAO => OK
        'familySupplement' => 'Complément familial', // ??
        'paidTraining' => 'Formation', // SI-SIAO => OK
        'youthGuarantee' => 'Garantie jeunes', // SI-SIAO => OK
        'dailyAllowance' => 'Indemnités journalières (IJ)', // SI-SIAO => OK
        'maintenance' => 'Pension alimentaire', // ??
        'disabilityPension' => 'Pension d\'invalidité', // ??
        'paje' => 'PAJE', // ??
        'pensionBenefit' => 'Retraite', // SI-SIAO => OK
        // 'ressourceOther' => 'Autre', // ??
    ];

    private const CHARGES_TYPE = [
        'insurance' => 'Assurance(s)', // SI-SIAO => OK
        // 'canteen' => 'Cantine', // ??
        'consumerCredit' => 'Crédit(s) à la consommation', // ??
        'water' => 'Eau', // SI-SIAO => OK
        'electricityGas' => 'Electricité / Gaz', // SI-SIAO => OK
        'childcare' => 'Garde d\'enfant(s)', // SI-SIAO => OK
        'taxes' => 'Impôts', // SI-SIAO => OK
        // 'rent' => 'Loyer', // SI-SIAO => OK
        'mutual' => 'Mutuelle(s)', // SI-SIAO => OK
        'alimony' => 'Pension alimentaire', // SI-SIAO => OK
        'phone' => 'Téléphone', // SI-SIAO => OK
        'transport' => 'Transport', // SI-SIAO => OK
        // 'chargeOther' => 'Autre charge', // ??
    ];

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
        $restToLive = round((($budgetBalanceAmt - $paymentAmt) / $nbConsumUnits / $this->nbDaysContribution), 2);

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
        $budgetBalanceAmt = $this->getBudgetBalanceAmt(self::RESOURCES_TYPE, self::CHARGES_TYPE);

        /** @var float Montant de la participation financière */
        $theoricalContribAmt = round($budgetBalanceAmt * $this->contributionRate, 0, PHP_ROUND_HALF_DOWN);

        /** @var float Reste à vivre journalier unitaire */
        $restToLive = round((($budgetBalanceAmt - $theoricalContribAmt) / $nbConsumUnits / self::NB_DAYS), 2);

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
        array $resourcesTypes = EvalBudgetPerson::RESOURCES_TYPE,
        array $chargesTypes = EvalBudgetPerson::CHARGES_TYPE
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
                    $resourcesGroupAmt += $this->getSumAmt($evalBudgetPerson, $resourcesTypes);
                    $chargesGroupAmt += $this->getSumAmt($evalBudgetPerson, $chargesTypes) + $evalBudgetPerson->getMonthlyRepaymentAmt();
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

    protected function getSumAmt(?EvalBudgetPerson $evalBudgetPerson, array $values): float
    {
        if (null === $evalBudgetPerson) {
            return 0;
        }

        $sumAmt = 0;
        foreach ($values as $key => $value) {
            $getRessMethod = 'get'.ucfirst($key);
            if (Choices::YES === $evalBudgetPerson->$getRessMethod()) {
                $getRessAmtMethod = $getRessMethod.'Amt';
                $sumAmt += $evalBudgetPerson->$getRessAmtMethod();
            }
        }

        return $sumAmt;
    }

    public function formatPrice($value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', ' ').' €';
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
               (new \DateTime())->modify('first day of last month'),
               $this->supportGroup->getStartDate()
            ));
        }
        if (!$this->payment->getEndDate()) {
            $this->payment->setEndDate(min(
               (clone $this->payment->getStartDate())->modify('last day of this month'),
               ($this->supportGroup->getEndDate() ?? (new \DateTime()))
            ));
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
