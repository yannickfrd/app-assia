<?php

namespace App\Form\Utils;

class Choices
{
    public const YES = 1;
    public const NO = 2;
    public const NO_INFORMATION = 99;

    public const YES_NO = [
        1 => 'Oui',
        2 => 'Non',
        99 => 'Non renseigné',
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
        99 => 'Non renseigné',
    ];

    public const DISABLE = [
        1 => 'Actif',
        2 => 'Désactivé',
        0 => 'Tous',
    ];

    public const ALL = 0;
    public const ACTIVE = 1;
    public const DISABLED = 2;

    public static function getchoices($const): array
    {
        $output = [];
        foreach ($const as $key => $value) {
            $output[$value] = $key;
        }

        return $output;
    }

    public static function getYears($number = 10): array
    {
        $years = [];
        $date = date('Y');

        for ($i = 0; $i < $number; ++$i) {
            $years[$date] = $date;
            --$date;
        }

        return $years;
    }
}
