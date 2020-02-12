<?php

namespace App\Form\Utils;

class Choices
{
    public const YES_NO = [
        1 => "Oui",
        2 => "Non",
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
