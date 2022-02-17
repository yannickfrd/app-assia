<?php

namespace App\Entity\Evaluation;

use App\Repository\Evaluation\ResourceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResourceRepository::class)
 */
class Resource extends AbstractFinance
{
    public const SALARY = 10;
    public const ARE = 30;
    public const IJ = 170;
    public const RSA = [60, 70];
    public const AF = 100;

    public const RESOURCES = [
        10 => 'Salaire', // 1 salary
        50 => 'Prime d\'activité', // 2 activityBonus
        30 => 'Allocation chômage (ARE)', // 3 unemplBenefit
        60 => 'RSA socle', // 4 minimumIncome
        70 => 'RSA majoré', // 4 minimumIncome
        100 => 'Allocations familiales (AF)', // 5 familyAllowance
        80 => 'Allocation adulte handicapé (AAH)', // 6 disAdultAllowance
        85 => 'Allocation d\'éducation de l\'enfant handicapé (AEEH)', // 7 disChildAllowance
        101 => 'Allocation de soutien familial (ASF)', // 8 asf
        90 => 'Allocation de solidarité spécifique (ASS)', //9 solidarityAllowance
        // 110 => 'Allocation temporaire d\'attente',
        130 => 'Allocation pour demandeur d\'asile (ADA)', // 10 asylumAllowance
        180 => 'Bourse', // 11 scholarships
        102 => 'Complément familial', //12 familySupplement
        40 => 'Formation rémunérée', // 13 paidTraining
        120 => 'Garantie jeunes', // 14 youthGuarantee
        170 => 'Indemnités journalières (IJ)', // 15 dailyAllowance
        200 => 'Pension alimentaire', // 16 maintenance
        210 => 'Pension d\'invalidité', // 17 disabilityPension
        103 => 'Prestation d\'accueil du jeune enfant (PAJE)', // 18 paje
        20 => 'Retraite', // 19 pensionBenefit
        1000 => 'Autre ressource', // 97 ressourceOther
    ];

    public const SHORT_RESOURCES = [
        10 => 'Salaire',
        50 => 'Prime d\'activité',
        30 => 'ARE',
        60 => 'RSA socle',
        70 => 'RSA majoré',
        100 => 'AF',
        80 => 'AAH',
        85 => 'AEEH',
        101 => 'ASF',
        90 => 'ASS',
        130 => 'ADA',
        180 => 'Bourse',
        102 => 'Complément familial',
        40 => 'Formation rémunérée',
        120 => 'Garantie jeunes',
        170 => 'IJ',
        200 => 'Pension alimentaire',
        210 => 'Pension d\'invalidité',
        103 => 'PAJE',
        20 => 'Retraite',
        1000 => 'Autre ressource',
    ];

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\ManyToOne(targetEntity=EvalBudgetPerson::class, inversedBy="resources")
     */
    private $evalBudgetPerson;

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getTypeToString(): ?string
    {
        return $this->type ? self::RESOURCES[$this->type] : null;
    }

    public function getEvalBudgetPerson(): ?EvalBudgetPerson
    {
        return $this->evalBudgetPerson;
    }

    public function setEvalBudgetPerson(?EvalBudgetPerson $evalBudgetPerson): self
    {
        $this->evalBudgetPerson = $evalBudgetPerson;

        return $this;
    }
}
