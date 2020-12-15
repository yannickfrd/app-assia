<?php

namespace App\Service;

class Grammar
{
    /**
     * Accorde en fonction du sexe de la personne (féminin, masculin).
     */
    public static function gender($gender): string
    {
        return 1 === $gender ? 'e' : '';
    }

    /**
     * Met un "s" si plusieurs éléments.
     */
    public static function plural($plural): string
    {
        return 1 === $plural ? 's' : '';
    }
}
