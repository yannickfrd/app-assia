<?php

namespace App\Service;

class Grammar
{
    /**
     * Accorde en fonction du sexe de la personne (féminin, masculin)
     * @return String
     */
    public static function gender($gender): String
    {
        return $gender == 1 ? "e" : "";
    }

    /**
     * Met un "s" si plusieurs éléments
     * @return String
     */
    public static function plural($plural): String
    {
        return $plural == 1 ? "s" : "";
    }
}
