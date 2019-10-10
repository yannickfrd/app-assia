<?php

namespace App\Utils;

class Agree
{
    /**
     * Accorde en fonction du sexe de la personne (féminin, masculin)
     * @return String
     */
    public static function gender($gender): String
    {
        if ($gender == 1) {
            return "e";
        }
        return "";
    }

    /**
     * Met un "s" si plusieurs éléments
     * @return String
     */
    public static function plural($plural): String
    {
        if ($plural > 1) {
            return "s";
        } 
        return "";
    }
}