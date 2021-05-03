<?php

namespace App\Service\Contribution;

use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Support\Contribution;
use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;

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

    /** @var int Unité de consommation (UC) pour le 1er adulte */
    public const UC_FIRST_ADULT = 1;
    /** @var int Unité de consommation (UC) pour les autres personnes de 14 ans ou plus */
    public const UC_OTHER_ADULT_OR_TEENAGE = 0.5;
    /** @var int Unité de consommation (UC) pour un enfant (moins de 14 ans) */
    public const UC_CHILD = 0.3;

    /** @var int Reste à vivre journalier unitaire minimum (12 euros) */
    public const RPV_MINIMAL = 12;

    public const RESOURCES_TYPE = [
        'salary' => 'Salaire', // SI-SIAO => OK
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
        'maintenance' => 'Pension alimentaire', // ??
        'disabilityPension' => 'Pension d\'invalidité', // ??
        'paje' => 'PAJE', // ??
        'activityBonus' => 'Prime d\'activité', // SI-SIAO => OK
        'pensionBenefit' => 'Retraite', // SI-SIAO => OK
        // 'ressourceOther' => 'Autre', // ??
    ];

    public const CHARGES_TYPE = [
        'insurance' => 'Assurance(s)', // SI-SIAO => OK
        // 'canteen' => 'Cantine', // ??
        'consumerCredit' => 'Crédit(s) à la consommation', // ??
        'water' => 'Eau', // SI-SIAO => OK
        'electricityGas' => 'Electricité / Gaz', // SI-SIAO => OK
        'childcare' => 'Garde d\'enfant(s)', // SI-SIAO => OK
        'taxes' => 'Impôts', // SI-SIAO => OK
        'rent' => 'Loyer', // SI-SIAO => OK
        'mutual' => 'Mutuelle(s)', // SI-SIAO => OK
        'alimony' => 'Pension alimentaire', // SI-SIAO => OK
        'phone' => 'Téléphone', // ??
        'transport' => 'Transport', // SI-SIAO => OK
        // 'chargeOther' => 'Autre charge', // ??
    ];

    public function calculate(SupportGroup $supportGroup, EvaluationGroup $evaluationGroup, ?Contribution $contribution): Contribution
    {
        if (!$contribution) {
            $contribution = new Contribution();
        }

        /** @var float Pourcentage appliqué sur le montant total des ressources prises en compte, déduction faite des charges */
        $contributionRate = $supportGroup->getService()->getContributionRate();

        /** @var float Nombre d'unité de consommation */
        $nbUc = $this->getNbUc($supportGroup);

        /** @var float Budget du ménage (ressources - charges) */
        $budgetBalanceAmt = $this->getBudgetBalanceAmt($evaluationGroup, $contribution);

        /** @var float Montant de la participation financière */
        $theoricalContribAmt = round($budgetBalanceAmt * $contributionRate, 0, PHP_ROUND_HALF_DOWN);

        /** @var float Reste à vivre journalier unitaire */
        $rav = ($budgetBalanceAmt - $theoricalContribAmt) / $nbUc / self::NB_DAYS;

        /** @var float Montant de participation financière */
        $contributionAmt = $theoricalContribAmt;

        if ($rav < self::RPV_MINIMAL || true === $contribution->getNoContrib()) {
            $contributionAmt = 0;
        }

        return $contribution
            ->setRate($contributionRate)
            ->setNbUC($nbUc)
            ->setTheoricalContribAmt($theoricalContribAmt)
            ->setRav($rav)
            ->setToPayAmt($contributionAmt);
    }

    /**
     * Get sum of consumer unit.
     */
    protected function getNbUc(SupportGroup $supportGroup): float
    {
        /** @var float */
        $nbUc = 0;
        /** @var bool */
        $firstAdult = true;

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getEndDate() != $supportGroup->getEndDate()) {
                continue;
            }

            $age = $supportPerson->getPerson()->getAge();

            if ($age >= self::AGE_ADULT && true === $firstAdult) {
                $nbUc += self::UC_FIRST_ADULT;
                $firstAdult = false;
            } elseif ($age >= self::AGE_TEENAGE && false === $firstAdult) {
                $nbUc += self::UC_OTHER_ADULT_OR_TEENAGE;
            } else {
                $nbUc += self::UC_CHILD;
            }
        }

        return $nbUc;
    }

    protected function getBudgetBalanceAmt(EvaluationGroup $evaluationGroup, Contribution $contribution): float
    {
        /** @var float Ressources du ménage */
        $resourcesGroupAmt = 0;
        /** @var float Charges du ménages */
        $chargesGroupAmt = 0;

        $endDateSupportGroup = $evaluationGroup->getSupportGroup()->getEndDate();

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            $supportPerson = $evaluationPerson->getSupportPerson();

            if ($supportPerson->getEndDate() != $endDateSupportGroup
                || $supportPerson->getPerson()->getAge() < self::AGE_ADULT) {
                continue;
            }

            $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();
            $resourcesGroupAmt += $this->getSumAmt($evalBudgetPerson, self::RESOURCES_TYPE);
            $chargesGroupAmt += $this->getSumAmt($evalBudgetPerson, self::CHARGES_TYPE);
        }

        if (null !== $contribution->getResourcesAmt()) {
            $resourcesGroupAmt = $contribution->getResourcesAmt();
        } else {
            $contribution->setResourcesAmt($resourcesGroupAmt);
        }

        if (null !== $contribution->getChargesAmt()) {
            $chargesGroupAmt = $contribution->getChargesAmt();
        } else {
            $contribution->setChargesAmt($chargesGroupAmt);
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
}
