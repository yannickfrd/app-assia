<?php

namespace App\Form\Utils;

class Choices
{
    public const YES_NO = [
        1 => "Oui",
        2 => "Non",
        99 => "Non renseigné"
    ];

    public const YES_NO_IN_PROGRESS = [
        1 => "Oui",
        2 => "Non",
        3 => "En cours de démarche",
        99 => "Non renseigné"
    ];

    public const YES_NO_PARTIAL = [
        1 => "Oui",
        2 => "Non",
        3 => "Partiellement",
        99 => "Non renseigné"
    ];

    public static function getchoices($const)
    {
        foreach ($const as $key => $value) {
            $output[$value] = $key;
        }
        return $output;
    }
}
