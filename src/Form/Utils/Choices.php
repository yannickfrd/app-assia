<?php

namespace App\Form\Utils;

class Choices
{
    public const YES_NO = [
        1 => 'Oui',
        2 => 'Non',
        99 => 'Non renseigné',
    ];

    public const YES_NO_IN_PROGRESS = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Démarche en cours',
        99 => 'Non renseigné',
    ];

    public const YES_NO_IN_PROGRESS_NC = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Démarche en cours',
        98 => 'Non concerné',
        99 => 'Non renseigné',
    ];

    public const YES_NO_PARTIAL = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Partiellement',
        99 => 'Non renseigné',
    ];

    public const YES_NO_BOOLEAN = [
        0 => 'Non',
        1 => 'Oui',
    ];

    public static function getchoices($const): array
    {
        $output = [];
        foreach ($const as $key => $value) {
            $output[$value] = $key;
        }

        return $output;
    }
}
