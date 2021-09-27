<?php

namespace App\Service\SiSiao;

trait SiSiaoClientTrait
{
    /**
     * Convert string date to Datetime object.
     */
    public static function convertDate(?string $date): ?\DateTime
    {
        if (null === $date) {
            return null;
        }

        if (str_contains($date, '-')) {
            return new \DateTime($date);
        }

        if (str_contains($date, '/')) {
            $dateArray = explode('/', $date);
            if (3 === count($dateArray)) {
                return new \DateTime($dateArray[2].'-'.$dateArray[1].'-'.$dateArray[0]);
            }
        }

        return null;
    }

    /**
     * @param int|object|null $needle
     *
     * @return int|string|null
     */
    public static function findInArray($needle, array $haystack)
    {
        if (!isset($needle)) {
            return null;
        }

        if (is_object($needle)) {
            if (isset($needle->id)) {
                $needle = $needle->id;
            }
        }

        foreach ($haystack as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }

        return null;
    }

    protected static function getFichePersonneId(object $personne): ?int
    {
        foreach ($personne->fiches as $fiche) {
            if ('Personne' === $fiche->typefiche) {
                return $fiche->id;
            }
        }

        return null;
    }
}
