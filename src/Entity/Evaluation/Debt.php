<?php

namespace App\Entity\Evaluation;

use App\Repository\Evaluation\DebtRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DebtRepository::class)
 */
class Debt extends AbstractFinance
{
    public const DEBTS = [
        50 => 'Amendes', // 4 | debtFines
        120 => 'Dettes d\'énergie', // debtEnergy
        20 => 'Dette de crédits à la consommation', // 2 | debtConsrCredit
        30 => 'Dettes de crédits immobiliers', // 3 | debtMortgage
        110 => 'Dettes de santé (hôpital)', // 5 | debtHealth
        10 => 'Dettes locatives', // 1 | debtRental
        70 => 'Découverts bancaires', // 7 | debtBankOverdrafts
        40 => 'Pension alimentaire non réglée',
        60 => 'Retards d\'impôts', // 6 | debtTaxDelays
        1000 => 'Autres dettes', // 97 | debtOther
    ];

    /**
     * @ORM\ManyToOne(targetEntity=EvalBudgetPerson::class, inversedBy="debts")
     */
    private $evalBudgetPerson;

    public function getEvalBudgetPerson(): ?EvalBudgetPerson
    {
        return $this->evalBudgetPerson;
    }

    public function setEvalBudgetPerson(?EvalBudgetPerson $evalBudgetPerson): self
    {
        $this->evalBudgetPerson = $evalBudgetPerson;

        return $this;
    }

    public function getTypeToString(): ?string
    {
        return $this->type ? self::DEBTS[$this->type] : null;
    }
}
