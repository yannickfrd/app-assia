<?php

namespace App\Form\Utils;

class EvaluationChoices
{
    public const YES = 1;
    public const NO = 2;
    public const IN_PROGRESS = 3;
    public const NO_INFORMATION = 99;

    public const YES_NO = [
        1 => 'Oui',
        2 => 'Non',
        99 => 'Non évalué',
    ];

    public const YES_NO_IN_PROGRESS = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Démarche en cours',
        99 => 'Non évalué',
    ];

    public const YES_NO_IN_PROGRESS_NC = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Démarche en cours',
        98 => 'Non concerné',
        99 => 'Non évalué',
    ];

    public const YES_NO_PARTIAL = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Partiellement',
        99 => 'Non évalué',
    ];

    public const YES_NO_BOOLEAN = [
        0 => 'Non',
        1 => 'Oui',
    ];

    public const DEPARTMENTS = [
        75 => '75',
        77 => '77',
        78 => '78',
        91 => '91',
        92 => '92',
        93 => '93',
        94 => '94',
        95 => '95',
        98 => 'Hors IDF',
        99 => 'Non évalué',
    ];
}
