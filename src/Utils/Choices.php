<?php

namespace App\Utils;

class Choices
{
    public static function getchoices($const)
    {
        foreach ($const as $key => $value) {
            $output[$value] = $key;
        }
        return $output;
    }
}
