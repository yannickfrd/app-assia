<?php

namespace App\Service;

use DateTime;
use Exception;

class Calendar
{
    public const MONTHS = [
        1 => "Janvier",
        2 => "Février",
        3 => "Mars",
        4 => "Avril",
        5 => "Mai",
        6 => "Juin",
        7 => "Juillet",
        8 => "Août",
        9 => "Septembre",
        10 => "Octobre",
        11 => "Novembre",
        12 => "Décembre"
    ];

    public const MONTHS_MIN = [
        1 => "jan.",
        2 => "fév.",
        3 => "mars",
        4 => "avr.",
        5 => "mai",
        6 => "juin",
        7 => "juil.",
        8 => "août",
        9 => "sept.",
        10 => "oct.",
        11 => "nov.",
        12 => "déc."
    ];

    public const DAYS = [
        1 => "Lundi",
        2 => "Mardi",
        3 => "Mercredi",
        4 => "Jeudi",
        5 => "Vendredi",
        6 => "Samedi",
        7 => "Dimanche"
    ];

    public const DAYS_MIN = [
        "Lun.",
        "Mar.",
        "Mer.",
        "Jeu.",
        "Ven.",
        "Sam.",
        "Dim."
    ];

    public $year;
    public $month;
    private $day;
    public $weeks;

    /**
     * Month constructor
     * 
     * @param integer $year
     * @param integer $month
     */
    public function __construct(?int $year = null, ?int $month = null, ?int $day = null)
    {
        if ($year === null) {
            $year = intval(date("Y"));
        }
        if ($month === null) {
            $month = intval(date("m"));
        }
        if ($day === null) {
            $day = intval(date("d"));
        }

        if ($year < 1970) {
            throw new \Exception("L'année est inférieure à 1970.");
        }
        if ($month < 1 || $month > 12) {
            throw new \Exception("Le mois $month n'est pas valide.");
        }
        if ($day < 1 || $day > 31) {
            throw new \Exception("Le jour $day n'est pas valide.");
        }

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;

        $this->setWeeks();
    }

    /**
     * Donne le mois et l'année en toute lettre
     *
     * @return string
     */
    public function getMonthToString(): string
    {
        return self::MONTHS[$this->month] . " " . $this->year;
    }

    /**
     * Donne la liste des jours
     *
     * @return array
     */
    public function getDaysList(): array
    {
        return self::DAYS_MIN;
    }

    /**
     * Donne le premier jour du mois
     *
     * @return \DateTime
     */
    public function getFirstDay(): \DateTime
    {
        return new \Datetime($this->year . "-" . $this->month . "-01");
    }

    /**
     * Donne le premier lundi
     * 
     * @return \DateTime
     */
    public function getFirstMonday(): \DateTime
    {
        if ($this->getFirstDay()->format("N") == "1") {
            return $this->getFirstDay();
        }
        return $this->getFirstDay()->modify("last monday");
    }

    /**
     * Donne le dernier jour du mois
     *
     * @return \DateTime
     */
    public function getLastDay(): \DateTime
    {
        return (clone $this->getFirstMonday())->modify("+" . ($this->getWeeks() * 7) - 1 .  " days");
    }

    /** Détermine le nombre de semaines dans le mois
     * 
     */
    public function setWeeks()
    {
        $startMonth = $this->getFirstDay();
        $endMonth = (clone $startMonth)->modify("+1 month -1 day");
        $weeks = intval($endMonth->format("W")) - intval($startMonth->format("W")) + 1;
        if (intval($endMonth->format("W")) == 1) {
            $weeks = 53 - intval($startMonth->format("W")) + 1;
        }
        if ($weeks < 0) {
            $weeks = intval($endMonth->format("W")) + 1;
        }
        $this->weeks = $weeks;
    }

    /**
     * Donne le nombre de semaine
     *
     * @return integer
     */
    public function getWeeks(): int
    {
        return $this->weeks;
    }

    /**
     * Retourne si le jour est est à l'intérieur du mois
     *
     * @param \datetime $date
     * @return boolean
     */
    public function withinMonth(\datetime $date): bool
    {
        return $this->getFirstDay()->format("m") === $date->format("m");
    }

    public function IsToday(\datetime $date): bool
    {
        $today = new \dateTime();
        return $date->format("Y-m-d") === $today->format("Y-m-d");
    }

    /**
     * Retourne l'abréviation du mois
     * 
     * @param \datetime $date
     * @return string
     */
    public function getOtherMonth(\datetime $date): string
    {
        if ($this->getFirstDay()->format("m") != $date->format("m")) {
            return self::MONTHS_MIN[intval($date->format("m"))];
        }
        return "";
    }

    /**
     * Retourne le mois précédent
     *
     * @return Array
     */
    public function previousMonth(): array
    {
        $month  = $this->month - 1;
        $year = $this->year;
        if ($month < 1) {
            $year  -= 1;
            $month = 12;
        }
        return [
            "year" => $year,
            "month" => $month
        ];
    }

    /**
     * Retourne le mois suivant
     *
     * @return Array
     */
    public function nextMonth(): array
    {
        $month  = $this->month + 1;
        $year = $this->year;

        if ($month > 12) {
            $year  += 1;
            $month = 1;
        }
        return [
            "year" => $year,
            "month" => $month
        ];
    }
}
