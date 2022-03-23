<?php

namespace App\Service;

class Calendar
{
    public const MONTHS = [
        1 => 'Janvier',
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre',
    ];

    public const MONTHS_MIN = [
        1 => 'jan.',
        2 => 'fév.',
        3 => 'mars',
        4 => 'avr.',
        5 => 'mai',
        6 => 'juin',
        7 => 'juil.',
        8 => 'août',
        9 => 'sept.',
        10 => 'oct.',
        11 => 'nov.',
        12 => 'déc.',
    ];

    public const DAYS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];

    public const DAYS_MIN = [
        'Lun.',
        'Mar.',
        'Mer.',
        'Jeu.',
        'Ven.',
        'Sam.',
        'Dim.',
    ];

    public $year;
    public $month;
    private $day;
    public $weeks;

    /**
     * Month constructor.
     */
    public function __construct(?int $year = null, ?int $month = null, ?int $day = null)
    {
        if (null === $year) {
            $year = intval(date('Y'));
        }
        if (null === $month) {
            $month = intval(date('m'));
        }
        if (null === $day) {
            $day = intval(date('d'));
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
     * Donne le mois et l'année en toute lettre.
     */
    public function getMonthToString(): string
    {
        return self::MONTHS[$this->month].' '.$this->year;
    }

    /**
     * Donne le mois et l'année en toute lettre.
     */
    public function getMonthMinToString(): string
    {
        return self::MONTHS_MIN[$this->month].' '.$this->year;
    }

    /**
     * Donne la liste des jours.
     */
    public function getDaysList(): array
    {
        return self::DAYS_MIN;
    }

    /**
     * Donne le premier jour du mois.
     */
    public function getFirstDayOfTheMonth(): \DateTime
    {
        return new \DateTime($this->year.'-'.$this->month.'-01');
    }

    /**
     * Donne le dernier jour du mois.
     */
    public function getLastDayOfTheMonth(): \DateTime
    {
        return (clone $this->getFirstDayOfTheMonth())->modify('last day of this month');
    }

    /**
     * Donne le premier lundi.
     */
    public function getFirstMonday(): \DateTime
    {
        if ('1' === $this->getFirstDayOfTheMonth()->format('N')) {
            return $this->getFirstDayOfTheMonth();
        }

        return $this->getFirstDayOfTheMonth()->modify('last monday');
    }

    /**
     * Donne le dernier jour du mois.
     */
    public function getLastDay(): \DateTime
    {
        return (clone $this->getFirstMonday())->modify('+'.(($this->getWeeks() * 7) - 1).' days');
    }

    /**
     * Détermine le nombre de semaines dans le mois.
     */
    public function setWeeks(): void
    {
        $startMonth = $this->getFirstDayOfTheMonth();
        $endMonth = (clone $startMonth)->modify('+1 month -1 day');
        $weeks = intval($endMonth->format('W')) - intval($startMonth->format('W')) + 1;
        if (1 === intval($endMonth->format('W'))) {
            $weeks = 53 - intval($startMonth->format('W')) + 1;
        }
        if ($weeks < 0) {
            $weeks = intval($endMonth->format('W')) + 1;
        }
        $this->weeks = $weeks;
    }

    /**
     * Donne le nombre de semaine.
     */
    public function getWeeks(): int
    {
        return $this->weeks;
    }

    /**
     * Retourne si le jour est est à l'intérieur du mois.
     */
    public function withinMonth(\DateTime $date): bool
    {
        return $this->getFirstDayOfTheMonth()->format('m') === $date->format('m');
    }

    public function IsToday(\DateTime $date): bool
    {
        return $date->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
    }

    /**
     * Retourne l'abréviation du mois.
     */
    public function getOtherMonth(\DateTime $date): string
    {
        if ($this->getFirstDayOfTheMonth()->format('m') != $date->format('m')) {
            return self::MONTHS_MIN[intval($date->format('m'))];
        }

        return '';
    }

    /**
     * Retourne le mois précédent.
     */
    public function previousMonth(): array
    {
        $month = $this->month - 1;
        $year = $this->year;

        if ($month < 1) {
            --$year;
            $month = 12;
        }

        return [
            'year' => $year,
            'month' => $month,
        ];
    }

    /**
     * Retourne le mois suivant.
     */
    public function nextMonth(): array
    {
        $month = $this->month + 1;
        $year = $this->year;

        if ($month > 12) {
            ++$year;
            $month = 1;
        }

        return [
            'year' => $year,
            'month' => $month,
        ];
    }
}
