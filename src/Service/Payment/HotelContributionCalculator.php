<?php

namespace App\Service\Payment;

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

class HotelContributionCalculator
{
    /** @var float Nombre de jours dans le mois (moyenne) */
    public const NB_DAYS = 30;

    public const RESOURCES_TYPE = [
        'salary' => 'Salaire', // SI-SIAO => OK
        'activityBonus' => 'Prime d\'activité', // SI-SIAO => OK
        'unemplBenefit' => 'ARE', // SI-SIAO => OK
        'minimumIncome' => 'RSA', // SI-SIAO => OK
        'familyAllowance' => 'AF', // SI-SIAO => OK
        'disAdultAllowance' => 'AAH', // SI-SIAO => OK
        // 'disChildAllowance' => 'AEEH', //SI-SIAO => OK
        'asf' => 'ASF', // ??
        'solidarityAllowance' => 'ASS', // SI-SIAO OK
        'asylumAllowance' => 'ADA', // SI-SIAO => OK
        // 'tempWaitingAllowance' => 'ATA', // SI-SIAO => OK
        'scholarships' => 'Bourse', // SI-SIAO => OK
        'familySupplement' => 'Complément familial', // ??
        'paidTraining' => 'Formation', // SI-SIAO => OK
        'youthGuarantee' => 'Garantie jeunes', // SI-SIAO => OK
        'dailyAllowance' => 'Indemnités journalières (IJ)', // SI-SIAO => OK
        'maintenance' => 'Pension alimentaire', // ??
        'disabilityPension' => 'Pension d\'invalidité', // ??
        // 'paje' => 'PAJE', // ??
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
        // 'rent' => 'Loyer', // SI-SIAO => OK
        'mutual' => 'Mutuelle(s)', // SI-SIAO => OK
        'alimony' => 'Pension alimentaire', // SI-SIAO => OK
        'phone' => 'Téléphone', // SI-SIAO => OK
        'transport' => 'Transport', // SI-SIAO => OK
        // 'chargeOther' => 'Autre charge', // ??
    ];
}
