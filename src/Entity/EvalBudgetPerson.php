<?php

namespace App\Entity;

use App\Entity\Traits\ResourcesEntityTrait;
use App\Form\Utils\Choices;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalBudgePersonRepository")
 */
class EvalBudgetPerson
{
    use ResourcesEntityTrait;

    public const SETTLEMENT_PLAN = [
        1 => 'Proposé',
        2 => 'Accepté',
        3 => 'Refusé',
        4 => 'En cours',
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $incomeN1Amt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $incomeN2Amt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $resourcesComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $charges;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $chargesAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $rent;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $electricityGas;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $water;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $insurance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $mutual;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $taxes;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $transport;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childcare;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $alimony;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $chargeOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $chargeOtherPrecision;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $rentAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $electricityGasAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $waterAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $insuranceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $mutualAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $taxesAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $transportAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $childcareAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $alimonyAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $phoneAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $chargeOtherAmt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $chargeComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debts;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debtRental;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debtConsrCredit;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debtMortgage;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debtFines;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debtTaxDelays;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debtBankOverdrafts;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debtOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $debtOtherPrecision;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $debtsAmt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $debtComment;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $monthlyRepaymentAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $overIndebtRecord;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $overIndebtRecordDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $settlementPlan;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $moratorium;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endRightsDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalBudget;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvaluationPerson", inversedBy="evalBudgetPerson", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $evaluationPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIncomeN1Amt(): ?float
    {
        return $this->incomeN1Amt;
    }

    public function setIncomeN1Amt(?float $incomeN1Amt): self
    {
        $this->incomeN1Amt = $incomeN1Amt;

        return $this;
    }

    public function getIncomeN2Amt(): ?float
    {
        return $this->incomeN2Amt;
    }

    public function setIncomeN2Amt(?float $incomeN2Amt): self
    {
        $this->incomeN2Amt = $incomeN2Amt;

        return $this;
    }

    public function getResourcesComment(): ?string
    {
        return $this->resourcesComment;
    }

    public function setResourcesComment(?string $resourcesComment): self
    {
        $this->resourcesComment = $resourcesComment;

        return $this;
    }

    public function getCharges(): ?int
    {
        return $this->charges;
    }

    /**
     * @Groups("export")
     */
    public function getChargesToString(): ?string
    {
        return $this->charges ? Choices::YES_NO[$this->charges] : null;
    }

    public function setCharges(?int $charges): self
    {
        $this->charges = $charges;

        return $this;
    }

    public function getChargesAmt(): ?float
    {
        return $this->chargesAmt;
    }

    public function setChargesAmt(?float $chargesAmt): self
    {
        $this->chargesAmt = $chargesAmt;

        return $this;
    }

    public function getRent(): ?int
    {
        return $this->rent;
    }

    public function setRent(?int $rent): self
    {
        $this->rent = $rent;

        return $this;
    }

    public function getElectricityGas(): ?int
    {
        return $this->electricityGas;
    }

    public function getElectricityGasToString(): ?string
    {
        return $this->electricityGas ? Choices::YES_NO_BOOLEAN[$this->electricityGas] : null;
    }

    public function setElectricityGas(?int $electricityGas): self
    {
        $this->electricityGas = $electricityGas;

        return $this;
    }

    public function getWater(): ?int
    {
        return $this->water;
    }

    public function getWaterToString(): ?string
    {
        return $this->water ? Choices::YES_NO_BOOLEAN[$this->water] : null;
    }

    public function setWater(?int $water): self
    {
        $this->water = $water;

        return $this;
    }

    public function getInsurance(): ?int
    {
        return $this->insurance;
    }

    public function getInsuranceToString(): ?string
    {
        return $this->insurance ? Choices::YES_NO_BOOLEAN[$this->insurance] : null;
    }

    public function setInsurance(?int $insurance): self
    {
        $this->insurance = $insurance;

        return $this;
    }

    public function getMutual(): ?int
    {
        return $this->mutual;
    }

    public function getMutualToString(): ?string
    {
        return $this->mutual ? Choices::YES_NO_BOOLEAN[$this->mutual] : null;
    }

    public function setMutual(?int $mutual): self
    {
        $this->mutual = $mutual;

        return $this;
    }

    public function getTaxes(): ?int
    {
        return $this->taxes;
    }

    public function getTaxesToString(): ?string
    {
        return $this->taxes ? Choices::YES_NO_BOOLEAN[$this->taxes] : null;
    }

    public function setTaxes(?int $taxes): self
    {
        $this->taxes = $taxes;

        return $this;
    }

    public function getTransport(): ?int
    {
        return $this->transport;
    }

    public function getTransportToString(): ?string
    {
        return $this->transport ? Choices::YES_NO_BOOLEAN[$this->transport] : null;
    }

    public function setTransport(?int $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getChildcare(): ?int
    {
        return $this->childcare;
    }

    public function getChildcareToString(): ?string
    {
        return $this->childcare ? Choices::YES_NO_BOOLEAN[$this->childcare] : null;
    }

    public function setChildcare(?int $childcare): self
    {
        $this->childcare = $childcare;

        return $this;
    }

    public function getAlimony(): ?int
    {
        return $this->alimony;
    }

    public function getAlimonyToString(): ?string
    {
        return $this->alimony ? Choices::YES_NO_BOOLEAN[$this->alimony] : null;
    }

    public function setAlimony(?int $alimony): self
    {
        $this->alimony = $alimony;

        return $this;
    }

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function getPhoneToString(): ?string
    {
        return $this->phone ? Choices::YES_NO_BOOLEAN[$this->phone] : null;
    }

    public function setPhone(?int $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getChargeOther(): ?int
    {
        return $this->chargeOther;
    }

    public function getChargeOtherToString(): ?string
    {
        return $this->chargeOther ? Choices::YES_NO_BOOLEAN[$this->chargeOther] : null;
    }

    public function setChargeOther(?int $chargeOther): self
    {
        $this->chargeOther = $chargeOther;

        return $this;
    }

    public function getChargeOtherPrecision(): ?string
    {
        return $this->chargeOtherPrecision;
    }

    public function setChargeOtherPrecision(?string $chargeOtherPrecision): self
    {
        $this->chargeOtherPrecision = $chargeOtherPrecision;

        return $this;
    }

    public function getRentAmt(): ?float
    {
        return $this->rentAmt;
    }

    public function setRentAmt(?float $rentAmt): self
    {
        $this->rentAmt = $rentAmt;

        return $this;
    }

    public function getElectricityGasAmt(): ?float
    {
        return $this->electricityGasAmt;
    }

    public function setElectricityGasAmt(?float $electricityGasAmt): self
    {
        $this->electricityGasAmt = $electricityGasAmt;

        return $this;
    }

    public function getWaterAmt(): ?float
    {
        return $this->waterAmt;
    }

    public function setWaterAmt(?float $waterAmt): self
    {
        $this->waterAmt = $waterAmt;

        return $this;
    }

    public function getInsuranceAmt(): ?float
    {
        return $this->insuranceAmt;
    }

    public function setInsuranceAmt(?float $insuranceAmt): self
    {
        $this->insuranceAmt = $insuranceAmt;

        return $this;
    }

    public function getMutualAmt(): ?float
    {
        return $this->mutualAmt;
    }

    public function setMutualAmt(?float $mutualAmt): self
    {
        $this->mutualAmt = $mutualAmt;

        return $this;
    }

    public function getTaxesAmt(): ?float
    {
        return $this->taxesAmt;
    }

    public function setTaxesAmt(?float $taxesAmt): self
    {
        $this->taxesAmt = $taxesAmt;

        return $this;
    }

    public function getTransportAmt(): ?float
    {
        return $this->transportAmt;
    }

    public function setTransportAmt(?float $transportAmt): self
    {
        $this->transportAmt = $transportAmt;

        return $this;
    }

    public function getChildcareAmt(): ?float
    {
        return $this->childcareAmt;
    }

    public function setChildcareAmt(?float $childcareAmt): self
    {
        $this->childcareAmt = $childcareAmt;

        return $this;
    }

    public function getAlimonyAmt(): ?float
    {
        return $this->alimonyAmt;
    }

    public function setAlimonyAmt(?float $alimonyAmt): self
    {
        $this->alimonyAmt = $alimonyAmt;

        return $this;
    }

    public function getPhoneAmt(): ?float
    {
        return $this->phoneAmt;
    }

    public function setPhoneAmt(?float $phoneAmt): self
    {
        $this->phoneAmt = $phoneAmt;

        return $this;
    }

    public function getChargeOtherAmt(): ?float
    {
        return $this->chargeOtherAmt;
    }

    public function setChargeOtherAmt(?float $chargeOtherAmt): self
    {
        $this->chargeOtherAmt = $chargeOtherAmt;

        return $this;
    }

    public function getChargeComment(): ?string
    {
        return $this->chargeComment;
    }

    public function setChargeComment(?string $chargeComment): self
    {
        $this->chargeComment = $chargeComment;

        return $this;
    }

    public function getDebts(): ?int
    {
        return $this->debts;
    }

    public function setDebts(?int $debts): self
    {
        $this->debts = $debts;

        return $this;
    }

    /**
     * @Groups("export")
     */
    public function getDebtsToString(): ?string
    {
        return $this->debts ? Choices::YES_NO[$this->debts] : null;
    }

    public function getDebtRental(): ?int
    {
        return $this->debtRental;
    }

    /**
     * @Groups("export")
     */
    public function getDebtRentalToString(): ?string
    {
        return $this->debtRental ? Choices::YES_NO_BOOLEAN[$this->debtRental] : null;
    }

    public function setDebtRental(?int $debtRental): self
    {
        $this->debtRental = $debtRental;

        return $this;
    }

    public function getDebtConsrCredit(): ?int
    {
        return $this->debtConsrCredit;
    }

    public function getDebtConsrCreditToString(): ?string
    {
        return $this->debtConsrCredit ? Choices::YES_NO_BOOLEAN[$this->debtConsrCredit] : null;
    }

    public function setDebtConsrCredit(?int $debtConsrCredit): self
    {
        $this->debtConsrCredit = $debtConsrCredit;

        return $this;
    }

    public function getDebtMortgage(): ?int
    {
        return $this->debtMortgage;
    }

    public function getDebtMortgageToString(): ?string
    {
        return $this->debtMortgage ? Choices::YES_NO_BOOLEAN[$this->debtMortgage] : null;
    }

    public function setDebtMortgage(int $debtMortgage): self
    {
        $this->debtMortgage = $debtMortgage;

        return $this;
    }

    public function getDebtFines(): ?int
    {
        return $this->debtFines;
    }

    public function getDebtFinesToString(): ?string
    {
        return $this->debtFines ? Choices::YES_NO_BOOLEAN[$this->debtFines] : null;
    }

    public function setDebtFines(?int $debtFines): self
    {
        $this->debtFines = $debtFines;

        return $this;
    }

    public function getDebtTaxDelays(): ?int
    {
        return $this->debtTaxDelays;
    }

    public function getDebtTaxDelaysToString(): ?string
    {
        return $this->debtTaxDelays ? Choices::YES_NO_BOOLEAN[$this->debtTaxDelays] : null;
    }

    public function setDebtTaxDelays(?int $debtTaxDelays): self
    {
        $this->debtTaxDelays = $debtTaxDelays;

        return $this;
    }

    public function getDebtBankOverdrafts(): ?int
    {
        return $this->debtBankOverdrafts;
    }

    public function getDebtBankOverdraftsToString(): ?string
    {
        return $this->getDebtBankOverdrafts() ? Choices::YES_NO_BOOLEAN[$this->debtBankOverdrafts] : null;
    }

    public function setDebtBankOverdrafts(?int $debtBankOverdrafts): self
    {
        $this->debtBankOverdrafts = $debtBankOverdrafts;

        return $this;
    }

    public function getDebtOther(): ?int
    {
        return $this->debtOther;
    }

    public function getDebtOtherToString(): ?string
    {
        return $this->debtOther ? Choices::YES_NO_BOOLEAN[$this->debtOther] : null;
    }

    public function setDebtOther(?int $debtOther): self
    {
        $this->debtOther = $debtOther;

        return $this;
    }

    public function getDebtOtherPrecision(): ?string
    {
        return $this->debtOtherPrecision;
    }

    public function setDebtOtherPrecision(?string $debtOtherPrecision): self
    {
        $this->debtOtherPrecision = $debtOtherPrecision;

        return $this;
    }

    public function getDebtsAmt(): ?float
    {
        return $this->debtsAmt;
    }

    public function setDebtsAmt(?float $debtsAmt): self
    {
        $this->debtsAmt = $debtsAmt;

        return $this;
    }

    public function getDebtComment(): ?string
    {
        return $this->debtComment;
    }

    public function setDebtComment(?string $debtComment): self
    {
        $this->debtComment = $debtComment;

        return $this;
    }

    public function getMonthlyRepaymentAmt(): ?float
    {
        return $this->monthlyRepaymentAmt;
    }

    public function setMonthlyRepaymentAmt(?float $monthlyRepaymentAmt): self
    {
        $this->monthlyRepaymentAmt = $monthlyRepaymentAmt;

        return $this;
    }

    public function getOverIndebtRecord(): ?int
    {
        return $this->overIndebtRecord;
    }

    /**
     * @Groups("export")
     */
    public function getOverIndebtRecordToString(): ?string
    {
        return $this->overIndebtRecord ? Choices::YES_NO_IN_PROGRESS[$this->overIndebtRecord] : null;
    }

    public function setOverIndebtRecord(?int $overIndebtRecord): self
    {
        $this->overIndebtRecord = $overIndebtRecord;

        return $this;
    }

    public function getOverIndebtRecordDate(): ?\DateTimeInterface
    {
        return $this->overIndebtRecordDate;
    }

    public function setOverIndebtRecordDate(?\DateTimeInterface $overIndebtRecordDate): self
    {
        $this->overIndebtRecordDate = $overIndebtRecordDate;

        return $this;
    }

    public function getSettlementPlan(): ?int
    {
        return $this->settlementPlan;
    }

    /**
     * @Groups("export")
     */
    public function getSettlementPlanToString(): ?string
    {
        return $this->settlementPlan ? self::SETTLEMENT_PLAN[$this->settlementPlan] : null;
    }

    public function setSettlementPlan(?int $settlementPlan): self
    {
        $this->settlementPlan = $settlementPlan;

        return $this;
    }

    public function getMoratorium(): ?int
    {
        return $this->moratorium;
    }

    /**
     * @Groups("export")
     */
    public function getMoratoriumToString(): ?string
    {
        return $this->moratorium ? Choices::YES_NO_IN_PROGRESS[$this->moratorium] : null;
    }

    public function setMoratorium(?int $moratorium): self
    {
        $this->moratorium = $moratorium;

        return $this;
    }

    public function getEndRightsDate(): ?\DateTimeInterface
    {
        return $this->endRightsDate;
    }

    public function setEndRightsDate(?\DateTimeInterface $endRightsDate): self
    {
        $this->endRightsDate = $endRightsDate;

        return $this;
    }

    public function getCommentEvalBudget(): ?string
    {
        return $this->commentEvalBudget;
    }

    public function setCommentEvalBudget(?string $commentEvalBudget): self
    {
        $this->commentEvalBudget = $commentEvalBudget;

        return $this;
    }

    public function getEvaluationPerson(): ?EvaluationPerson
    {
        return $this->evaluationPerson;
    }

    public function setEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        $this->evaluationPerson = $evaluationPerson;

        return $this;
    }
}
