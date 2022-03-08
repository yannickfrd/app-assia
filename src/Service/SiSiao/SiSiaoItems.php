<?php

namespace App\Service\SiSiao;

use App\Entity\People\Person;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;

/**
 * All items form API SI-SIAO and correspondence table with application.
 */
class SiSiaoItems
{
    public const YES = 'OUI';
    public const NO = 'NON';

    public const YES_NO = [
        'OUI' => Choices::YES,
        'NON' => Choices::NO,
        'EN_COURS' => EvaluationChoices::IN_PROGRESS,
        'A_FAIRE' => 98,
        'NON_RENSEIGNE' => null,
        'NR' => null,
    ];

    public const YES_NO_STRING_TO_BOOL = [
        'OUI' => true,
        'NON' => false,
    ];

    public const YES_NO_BOOL = [
        true => Choices::YES,
        false => Choices::NO,
    ];

    public const GENDERS = [
        'FEMME' => Person::GENDER_FEMALE,
        'HOMME' => Person::GENDER_MALE,
        '' => null,
    ];

    // SITUATION_DEMANDES
    public const HOUSING_STATUS = [
    47 => 97, // Accueil de jour, service social, associations
    20 => 001, // À la rue
    49 => 97, // Associations
    21 => 401, // AUDA
    44 => 97, // Autre
    22 => 400, // CADA
    23 => 401, // CHUDA
    24 => 304, // Colocation
    25 => 500, // Détention
    26 => 105, // Dispositif hivernal
    27 => 602, // Dispositif médical (LHSS / LAM, autre)
    55 => 300, // Domicile conjugal
    28 => 003, // Errance résidentielle
    54 => 004, // Evacuation de camp / bidonville
    45 => 300, // Expulsion locative du privé
    46 => 301, // Expulsion locative du public
    52 => 002, // Expulsion squat
    57 => 97, // Fin de prise en charge Mission hébergement logement (MHL)
    29 => 010, // Hébergé chez amis - autre
    30 => 011, // Hébergé chez famille
    33 => 103, // Hébergement de stabilisation
    31 => 104, // Hébergement d'insertion
    32 => 102, // Hébergement d'urgence
    35 => 100, // Hôtel 115
    34 => 101, // Hôtel (hors 115)
    56 => 401, // HUDA
    36 => 600, // Institutions publiques (hôpital, maison de retraite)
    51 => 206, // Intermédiation locative
    37 => 300, // Location parc privé
    38 => 301, // Location parc public
    39 => 204, // Logement accompagné
    40 => 207, // Logement foyer
    41 => 203, // Maison relais
    53 => 97, // MHL (mission hébergement logement)
    42 => 97, // PEC-ASE
    50 => 303, // Propriétaire
    43 => 204, // Résidence Sociale
    48 => 97, // Service social
    ];

    // MOTIF_DEMANDES
    public const REASON_REQUEST = [
        201 => 1, // Absence de ressources
        202 => 8, // Arrivée en France
        221 => 97, // Autre
        222 => 8, // Départ du département initial
        203 => 3, // Dort dans la rue
        223 => 97, // Evacuation de camp/bidonville ****
        204 => 9, // Expulsion locative
        225 => 97, // Expulsion SQUAT ****
        224 => 97, // Fin de PEC MHL (mission hébergement logement) ****
        207 => 10, // Fin de prise en charge ASE
        208 => 13, // Fin de prise en charge Conseil Général
        205 => 11, // Fin d'hébergement chez des tiers
        206 => 12, // Fin d'hospitalisation
        209 => 15, // Inadaptation du logement
        210 => 16, // Logement insalubre
        211 => 17, // Logement repris par le propriétaire
        212 => 18, // Rapprochement du lieu de travail
        213 => 19, // Regroupement familial
        214 => 20, // Risque d'expulsion locative
        215 => 21, // Séparation ou rupture des liens familiaux
        218 => 22, // Sortie de détention
        219 => 23, // Sortie de Logement accompagné
        217 => 24, // Sortie d'hébergement
        216 => 25, // Sortie dispositif asile
        220 => 27, // Violences familiales-conjugales
    ];

    // DUREE_ERRANCES
    public const WANDERING_TIME = [
        10 => 1, // Jour même
        20 => 1, // Moins d’une semaine
        30 => 2, // 1 semaine - 1 mois
        40 => 3, // 1 mois - 6 mois
        50 => 4, // 6 mois - 1 an
        60 => 5, // 1 an - 2 ans
        70 => 6, // 2 ans - 5 ans
        80 => 7, // 5 ans – 10 ans
        90 => 8, // Plus de 10 ans
        100 => null, // NSP
        110 => null, // Non Renseigné
        120 => null, // Refus de répondre
        130 => null, // Prochainement en demande
    ];

    // COMPOSITIONS
    public const FAMILY_TYPOLOGY = [
        100 => 6, // Couple avec enfant
        30 => 3, // Couple sans enfant
        120 => 9, // Enfant / Mineur en famille
        80 => 9, // Enfant / Mineur isolé
        90 => 9, // Enfants / Mineurs en groupe
        20 => 1, // Femme seule
        40 => 4, // Femme seule avec enfant(s)
        60 => 8, // Groupe avec enfant(s)
        70 => 7, // Groupe d'adultes sans enfant
        10 => 2, // Homme seul
        50 => 5, // Homme seul avec enfant(s)
    ];

    // SITUATION_PERSONNES
    public const ROLE = [
        10 => 5, // Célibataire
        50 => 2, // Concubinage
        70 => 3, // Divorcé
        100 => 3, // Enfant / Mineur en famille
        90 => 3, // Enfant / Mineur isolé
        20 => 2, // Marié(e)
        1 => 99, // Non renseignée
        30 => 1, // Pacsé(e)
        60 => 4, // Séparé
        80 => 4, // Veuf
        40 => 1, // Vie maritale
    ];

    // SITUATION_PERSONNES
    public const MARITAL_STATUS = [
        10 => 1, // Célibataire
        50 => 2, // Concubinage
        70 => 3, // Divorcé
        100 => 97, // Enfant / Mineur en famille
        90 => 97, // Enfant / Mineur isolé
        20 => 4, // Marié(e)
        1 => null, // Non renseignée
        30 => 5, // Pacsé(e)
        60 => 6, // Séparé
        80 => 7, // Veuf
        40 => 2, // Vie maritale
    ];

    // SITUATION_SORTIE_DEMANDE
    public const END_STATUS = [
        1 => 97, // Autre motif
        2 => 304, // Colocation
        3 => 400, // Dispositif d'asile
        4 => 105, // Dispositif hivernal
        5 => 602, // Sortie vers LAM (Lit Accueil Médicalisé)
        6 => 900, // Décédée
        7 => 010, // Hébergée par des tiers
        8 => 104, // Sortie vers hébergement d'insertion
        9 => 102, // Hébergement d'urgence
        10 => 103, // Sortie vers hébergement de stabilisation
        11 => 100, // Hôtel
        12 => 206, // Logement en intermédiation locative
        13 => 201, // Sortie vers Logement foyers (FJT - FTM)
        14 => 300, // Accès à un logement parc privé
        15 => 301, // Accès à un logement parc public
        16 => 200, // Sortie vers ALT
        17 => 203, // Sortie vers une maison relais
        18 => 700, // Départ volontaire de la personne
        19 => 701, // Exclusion de la structure
        20 => 011, // Retour au domicile conjugal ou personnel ****
        21 => 011, // Retour dans la famille
        22 => 001, // Rue/Abris de fortune (squat, camping, voiture)
        23 => 204, // Sortie vers résidence sociale
        24 => 207, // Résidence accueil ****
        25 => 704, // Retour dans le pays d'origine
        26 => 600, // Hospitalisation
        27 => 500, // Incarcération
        28 => null, // Information non renseignée
        29 => 601, // Sortie vers une unité de lits halte soins santé
        30 => 97, // Sortie vers les ACT (Appartement de Coordination Thérapeutique) ****
        31 => 97, // La personne a trouvé une autre solution
        32 => 400, // Sortie vers pré-CADA ****
        33 => 400, // Sortie vers un CADA
        34 => 106, // Sortie vers un centre maternel
        35 => 206, // Sortie vers ILM mandat de gestion ****
        36 => 206, // Sortie vers IML location sous location ****
        37 => 206, // Sortie vers IML location sous location bail glissant ****
        38 => 104, // Sortie vers un CHRS
        39 => 97, // Abscence momentanée prévue
        40 => 001, // Fermeture structure hivernale
        41 => 702, // Fin de séjour
        42 => null, // La personne n'a pas rappelé le 115
        43 => 97, // La personne ne s'est pas présentée
        44 => 97, // Prise en charge dans un autre département
        45 => 011, // Retour au domicile parental
        46 => 305, // Maison de retraite
        47 => 97, // Problème de mobilité (handicap) ****
        48 => 001, // Fermeture structure
        49 => 501, // Assignation à résidence ****
        50 => 102, // Sortie vers une structure d'urgence
        51 => 301, // Accès à un logement
        52 => 97, // Institutions publiques (hôpital, prison, maison de retraite...) ****
    ];

    public const NATIONALITY = [
        'FRANCAISE' => 1,
        'UE' => 2,
        'HORS_UE' => 3,
        'APATRIDE' => 4,
        'NR' => null,
    ];

    // PAPIER_IDENTITES
    public const PAPER = [
        110 => Choices::YES, // Autorisations provisoires de séjour
        40 => Choices::YES, // Carte de résident
        60 => Choices::YES, // Carte de séjour temporaire
        10 => Choices::YES, // Demandeur d'asile
        120 => Choices::YES, // Document de circulation pour mineur étranger
        90 => Choices::YES, // Français
        50 => null, // Non renseignée
        48 => Choices::YES, // Récépissé de demande de titre de séjour (1ère demande + renouvellement)
        81 => Choices::NO, // Situation administrative non régularisée
        100 => Choices::YES, // UE
    ];

    // DROIT_SEJOURS
    public const PAPER_TYPE = [
        110 => 22, // Autorisations provisoires de séjour
        40 => 20, // Carte de résident
        60 => 21, // Carte de séjour temporaire
        10 => 10, // Demandeur d'asile
        120 => 40, // Document de circulation pour mineur étranger
        90 => 01, // Français
        50 => null, // Non renseignée
        48 => 30, // Récépissé de demande de titre de séjour (1ère demande + renouvellement)
        81 => 97, // Situation administrative non régularisée
        100 => 03, // UE
    ];

    // DROIT_SEJOURS
    public const ASYLUM_BACKGROUND = [
        70 => null, // Autre
        20 => Choices::YES, // Bénéficiaire protection internationale/réfugié
        40 => null, // Carte de résident
        60 => null, // Carte de séjour temporaire
        30 => Choices::YES, // Débouté du droit d'asile
        10 => Choices::YES, // Demandeur d'asile
        50 => null, // Non renseignée
        48 => null, // Récépissé première demande de titre de séjour
        47 => null, // Récépissé renouvellement titre
        81 => null, // Situation administrative non régularisée
        80 => null, // Situation administrative régulière
    ];

    // // DROIT_SEJOURS
    // public const PAPER_TYPE_ASYLUM_STATUS = [
    //     70 => null, // Autre
    //     20 => null, // Bénéficiaire protection internationale/réfugié
    //     40 => 20, // Carte de résident
    //     60 => 21, // Carte de séjour temporaire
    //     30 => null, // Débouté du droit d'asile
    //     10 => null, // Demandeur d'asile
    //     50 => null, // Non renseignée
    //     48 => 30, // Récépissé première demande de titre de séjour
    //     47 => 31, // Récépissé renouvellement titre
    //     81 => null, // Situation administrative non régularisée
    //     80 => null, // Situation administrative régulière
    // ];

    // // DROIT_SEJOURS
    // public const ASYLUM_STATUS = [
    //     70 => null, // Autre
    //     20 => 4, // Bénéficiaire protection internationale/réfugié
    //     40 => null, // Carte de résident
    //     60 => null, // Carte de séjour temporaire
    //     30 => 1, // Débouté du droit d'asile
    //     10 => 2, // Demandeur d'asile
    //     50 => null, // Non renseignée
    //     48 => null, // Récépissé première demande de titre de séjour
    //     47 => null, // Récépissé renouvellement titre
    //     81 => null, // Situation administrative non régularisée
    //     80 => null, // Situation administrative régulière
    // ];

    // TYPE_HEBERGEMENT_ENFANT
    public const CHILD_TO_HOST = [
        2 => 2, // En garde alternée
        1 => 1, // En permanence
        4 => 3, // Journée uniquement
        3 => 4, // Uniquement WE et congés
    ];

    // REGROUPEMENT_FAMILIAL
    public const FAML_REUNIFICATION = [
        5 => 5, // Accepté
        4 => 4, // En cours
        3 => 3, // Envisagé
        2 => 2, // Non
        1 => null, // Non renseigné
    ];

    // DROIT_OUVERT_SECURITE_SOCIALES
    public const RIGHT_SOCIAL_SECURITY = [
        15 => Choices::YES, // Aide Complémentaire Santé (ACS)
        10 => Choices::YES, // Aide médicale de l'Etat
        200 => Choices::YES, // Autre
        20 => Choices::YES, // CMU
        30 => Choices::YES, // CMU complémentaire
        70 => Choices::YES, // Couverture sociale européenne
        40 => Choices::YES, // Mutuelle
        1 => null, // Non renseignée
        190 => Choices::YES, // NSP
        60 => Choices::YES, // Régime agricole
        50 => Choices::YES, // Régime général
        90 => Choices::YES, // Régime social des indépendants
        80 => Choices::NO, // Sans couverture sociale
    ];

    // DROIT_OUVERT_SECURITE_SOCIALES
    public const SOCIAL_SECURITY = [
        40 => 2, // Mutuelle
        30 => 4, // CMU-C -> CSS (ex-CMU-C)
        15 => 6, // Aide Complémentaire Santé (ACS)
        10 => 5, // Aide médicale de l'Etat (AME)
        20 => 3, // CMU -> PUMA (ex-CMU)
        50 => 1, // Régime général
        90 => 1, // Régime social des indépendants -> Régime général
        60 => 97, // Régime agricole
        70 => 97, // Couverture sociale européenne
        200 => 97, // Autre
        190 => null, // NSP
        80 => null, // Sans couverture sociale
        1 => null, // Non renseigné
    ];

    // Animaux
    public const ANIMAL = [
        2 => Choices::YES, // Chien
        3 => Choices::YES, // Chat
        1 => Choices::YES, // Autres
    ];

    public const ANINMAL_TYPE = [
        2 => 'Chien', // Chien
        3 => 'Chat', // Chat
        1 => null, // Autres
    ];

    // TYPE_CONTRATS
    public const CONTRACT_TYPE = [
        80 => 4, // Apprenti
        30 => 1, // CDD
        20 => 2, // CDI
        50 => 3, // Contrat aidé
        40 => 6, // Fonctionnaire
        60 => 7, // Intérim
        10 => null, // Non renseignée
        70 => 97, // Saisonnier
        90 => 8, // Stagiaire
    ];

    // TEMPS_TRAVAIL
    public const WORKING_TIME = [
        'COMPLET' => 1,
        'PARTIEL' => 2,
        'NR' => null,
        null => null,
    ];

    public const RESOURCES = [
        10 => 10, // Ressources d'activité
        20 => 20, // Retraite
        30 => 30,  // Allocations chômage
        40 => 40, // Formation
        50 => 50, // Prime d'activité
        60 => 60, // RSA Socle
        70 => 70, // RSA Majoré
        80 => 80, // AAH
        90 => 90, // ASS
        100 => 100, // Allocations familliales
        // 110 => 1000, // Allocation temporaire d'attente
        120 => 120, // Garantie jeune
        130 => 130, // Allocation pour demandeur d'asile
        140 => 60, // En attente RSA
        // 150 => null, // Non renseigné
        // 160 => null, // Refus de répondre
        170 => 170, // Indemnités journalières
        180 => 180, // Bourses
        1000 => 1000, // Autres ressources
    ];

    public const CHARGES = [
        10 => 10, // Loyer
        20 => 20, // EDF
        30 => 30, // GDF
        40 => 40, // Eau
        50 => 50, // Assurance
        60 => 60, // Mutuelle
        70 => 70, // Impôts
        80 => 80, // Transports
        90 => 90, // Garde enfant(s)
        100 => 100, // Pension alimentaire
        110 => 110, // Téléphone
        // 120 => 120, //  Non renseigné
        1000 => 1000, // Autres charges
    ];

    public const DEBTS = [
        10 => 10, // Dettes locatives
        20 => 20, // Dettes de crédits à la consommation
        30 => 30, // Dettes de crédits immobiliers
        40 => 40,  // Pension alimentaire non réglée
        50 => 50, // Amendes
        60 => 60, // Retards d'impôts
        70 => 70, // Découverts bancaires
        1000 => 1000, // Autres dettes
    ];

    // EVOLUTIONS_BUDGETAIRES
    public const EVOLUTIONS_BUDGETAIRES = [
        3 => null, // En augmentation
        4 => null, // En diminution
        1 => null, // Non renseignée
        2 => null, // Stable
    ];

    public const PREGNANCY_TYPE = [
        'SIMPLE' => 1,
        'JUMEAUX' => 2,
        'MULTIPLE' => 3,
    ];

    public const DOM_VIOLENCE_VICTIM = [
        'FEMME_VVC' => Choices::YES,
        'HOMME_VVC' => Choices::YES,
    ];

    public const ASE_STATUS = [
        'ACTIVE' => 'Suivi en cours',
        'INACTIVE' => 'Suivi terminé',
    ];

    public const SIAO_STATUS = [
        'ACTIVE' => 'Suivi en cours',
        'INACTIVE' => 'Suivi terminé',
    ];

    public const SYPLO_STATUS = [
        'INSCRIT' => Choices::YES,
        'NON_INSCRIT' => Choices::NO,
        'NE_RELEVE_PAS' => 98,
    ];

    // codeDepartement
    public const DEPARTMENTS = [
        '075' => 75,
        '077' => 77,
        '078' => 78,
        '091' => 91,
        '092' => 92,
        '093' => 93,
        '094' => 94,
        '095' => 95,
    ];

    // MESURE_ACCOMPAGNEMENT
    public const MESURE_ACCOMPAGNEMENT = [
        1 => null, // AVDL
        2 => null, // ASLL
        3 => null, // ALJ
        4 => null, // AHM
        5 => null, // Autre
        6 => null, // FSL
        7 => null, // AEL
    ];

    // DISPOSITIF
    public const DISPOSITIF = [
        2 => null, // Logement
        0 => null, // Non renseigné
        1 => null, // Hébergement
        5 => null, // Accompagnement
    ];

    // CATEGORIE_PLACES
    public const CATEGORIE_PLACES = [
        40 => null, // Chambre 2 places
        50 => null, // Chambre 3 places
        60 => null, // Chambre 4 places
        70 => null, // Chambre 5 et +
        10 => null, // Chambre collective
        180 => null, // Chambre hôtel
        1 => null, // Chambre individuelle
        20 => null, // Dortoir 5 et +
        80 => null, // Logement modulable
        90 => null, // Logement T1
        200 => null, // Logement T1 bis
        100 => null, // Logement T2
        110 => null, // Logement T3
        120 => null, // Logement T4
        130 => null, // Logement T5
        140 => null, // Logement T6
        150 => null, // Logement T7
        160 => null, // Logement T8
        170 => null, // Logement T9
        190 => null, // Studio
    ];

    // TYPE_PLACES
    public const TYPE_PLACES = [
        80 => null, // Abris de nuit
        70 => null, // Demandeur asile
        60 => null, // Femmes VV
        90 => null, // Hébergement période de grand froid (Gymnase)
        20 => null, // Hébergement urgence place hiver
        1 => null, // Hôtel
        110 => null, // Information non renseignée
        50 => null, // Lit halte santé
        100 => null, // Logement
        40 => null, // Place de stabilisation
        30 => null, // Place d'insertion
        10 => null, // Place en urgence
    ];

    // TYPES_ETABLISSEMENT_UN
    public const TYPES_ETABLISSEMENT_UN = [
        2000 => null, // Non renseigné
        2001 => 10, // Non renseigné (Hébergement) - Hébergement
        100 => 10, // Hôtels - Hébergement
        200 => 10, // Résidence Hôtelière à Vocation Sociale (RHVS) - Hébergement
        300 => 102, // Hors CHRS - Hébergement
        400 => 104, // CHRS - Hébergement
        600 => 10, // Hébergement spécialisé - Hébergement
        700 => 400, // Dispositif national d'accueil - Hébergement
        500 => 20, // Structure en ALT (Hébergement) - Hébergement
        2002 => 20, // Non renseigné (Logement) - Logement
        800 => 20, // Logement foyer - Logement
        900 => 206, // Intermédiation locative - Logement
        1000 => 30, // Logement de droit commun - Logement
        1100 => 20, // Structure en ALT (Logement) - Logement
    ];
}
