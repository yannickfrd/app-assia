<?php

namespace App\Entity\Evaluation;

use App\Repository\Evaluation\EvalBudgetChargeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EvalBudgetChargeRepository::class)
 */
class EvalBudgetCharge extends AbstractFinance
{
    public const CANTEEN = 220;
    public const INSURANCE = 50;
    public const PHONE = 110;
    public const REPAYMENT_DEBT = 260;
    public const TAXES = 70;
    public const TRANSPORT = 80;

    public const CHARGES = [
        50 => 'Assurance(s)', // 1 | insurance
        220 => 'Cantine', // 2 | canteen
        270 => 'Carburant',
        230 => 'Crédit(s) à la consommation', // 3 | consumerCredit
        40 => 'Eau', // 4 | water
        20 => 'Electricité', // 5 | electricityGas
        30 => 'Gaz', // 5 | electricityGas
        90 => 'Garde d\'enfant(s)', // 6 | childcare
        250 => 'Internet',
        70 => 'Impôts', // 7 | taxes
        10 => 'Loyer', // 8 | rent
        60 => 'Mutuelle(s)', // 9 | mutual
        240 => 'Participation financière',
        100 => 'Pension alimentaire', // 10 | alimony
        260 => 'Remboursement de dette(s)', // 13 | repaymentDebts
        110 => 'Téléphone', // 11 | phone
        80 => 'Transport', // 12 | transport
        280 => 'Frais de scolarité',
        1000 => 'Autre charge', // 97 | chargeOther
    ];

    /**
     * @ORM\ManyToOne(targetEntity=EvalBudgetPerson::class, inversedBy="evalBudgetCharges")
     */
    private $evalBudgetPerson;

    public function getTypeToString(): ?string
    {
        return $this->type ? self::CHARGES[$this->type] : null;
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
