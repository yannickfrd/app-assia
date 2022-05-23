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
    /** @var int Nombre de jours dans le mois (moyenne) */
    public const NB_DAYS = 30;

    public const RESOURCES = [
        10 => 'Salaire', // SI-SIAO => OK
        50 => 'Prime d\'activité', // SI-SIAO => OK
        30 => 'Allocation chômage (ARE)', // SI-SIAO => OK
        60 => 'RSA', // SI-SIAO => OK
        100 => 'Allocations familiales (AF)', // SI-SIAO => OK
        80 => 'Allocation adulte handicapé (AAH)', // SI-SIAO => OK
        // 85 => 'Allocation d\'éducation de l\'enfant handicapé (AEEH)',
        101 => 'Allocation de soutien familial (ASF)', // ??
        90 => 'Allocation de solidarité spécifique (ASS)', // SI-SIAO => OK
        130 => 'Allocation pour demandeur d\'asile (ADA)', // SI-SIAO => OK
        180 => 'Bourse', // SI-SIAO => OK
        102 => 'Complément familial',
        40 => 'Formation rémunérée', // SI-SIAO => OK
        120 => 'Garantie jeunes', // SI-SIAO => OK
        170 => 'Indemnités journalières (IJ)', // SI-SIAO => OK
        200 => 'Pension alimentaire',
        210 => 'Pension d\'invalidité',
        // 103 => 'Prestation d\'accueil du jeune enfant (PAJE)', // ??
        20 => 'Retraite', // SI-SIAO => OK
        // 1000 => 'Autre ressource',
    ];

    public const CHARGES = [
        50 => 'Assurance(s)', // SI-SIAO => OK
        // 220 => 'Cantine',
        // 270 => 'Carburant',
        290 => 'Charges de vie courante',
        230 => 'Crédit(s) à la consommation', // ??t
        40 => 'Eau', // SI-SIAO => OK
        20 => 'Electricité', // SI-SIAO => OK
        30 => 'Gaz', // SI-SIAO => OK
        90 => 'Garde d\'enfant(s)', // SI-SIAO => OK
        250 => 'Internet',
        70 => 'Impôts', // SI-SIAO => OK
        // 10 => 'Loyer', // SI-SIAO => OK
        60 => 'Mutuelle(s)', // SI-SIAO => OK
        // 240 => 'Participation financière',
        100 => 'Pension alimentaire versée', // SI-SIAO => OK
        260 => 'Remboursement de dette(s)', // SI-SIAO => OK
        110 => 'Téléphone', // SI-SIAO => OK
        80 => 'Transport', // SI-SIAO => OK
        // 280 => 'Frais de scolarité',
        // 1000 => 'Autre charge',
    ];
}
