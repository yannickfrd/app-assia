<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SitBudgetRepository")
 */
class SitBudget
{
    public const OVER_INDEBT_RECCORD = [
        1 => "Oui",
        2 => "Non",
        3 => "En cours",
        99 => "Non renseigné"
    ];

    public const SETTLEMENT_PLAN = [
        1 => "Proposé",
        2 => "Accepté",
        3 => "Refusé",
        4 => "En cours",
        99 => "Non renseigné"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $ressources;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ressourcesAmt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $disAdultAlw;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $disChildAlw;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $unemplBenf;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $asylumSeekerAlw;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $tempWaitingAlw;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $familyAlw;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $solidarityAlw;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $paidTraining;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $youthGuarantee;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $maintenance;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $activityBonus;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $pensionBenf;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $minIncome;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $salary;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $ressourceOther;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ressourceOtherPrecision;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $disAdultAlwAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $disChildAlwAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $unemplBenfAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $asylumSeekerAlwAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tempWaitingAlwAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $familyAlwAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $solidarityAlwAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $paidTrainingAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $youthGuaranteeAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maintenanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $activityBonusAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pensionBenfAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minIncomeAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $salaryAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ressourceOtherAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $taxIncomeN1;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $taxIncomeN2;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $ressourcesComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $charges;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $chargesAmt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $rent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $electricityGas;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $water;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $insurance;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mutual;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $taxes;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $transport;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $childcare;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $alimony;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean", nullable=true)
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $debtRental;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $debtConsrCredit;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $debtMortgage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $debtFines;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $debtTaxDelays;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $debtBankOverdrafts;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $debtOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $debtOtherPrecision;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $debtsAmt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $debtComment;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $monthlyRepaymentAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $overIndebtRecord;

    /**
     * @ORM\Column(type="date", nullable=true)
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentSitBudget;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportPerson", inversedBy="sitBudget", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRessources(): ?float
    {
        return $this->ressources;
    }

    public function setRessources(?float $ressources): self
    {
        $this->ressources = $ressources;

        return $this;
    }

    public function getRessourcesAmt(): ?float
    {
        return $this->ressourcesAmt;
    }

    public function setRessourcesAmt(?float $ressourcesAmt): self
    {
        $this->ressourcesAmt = $ressourcesAmt;

        return $this;
    }

    public function getDisAdultAlw(): ?bool
    {
        return $this->disAdultAlw;
    }

    public function setDisAdultAlw(?bool $disAdultAlw): self
    {
        $this->disAdultAlw = $disAdultAlw;

        return $this;
    }

    public function getDisChildAlw(): ?bool
    {
        return $this->disChildAlw;
    }

    public function setDisChildAlw(?bool $disChildAlw): self
    {
        $this->disChildAlw = $disChildAlw;

        return $this;
    }

    public function getUnemplBenf(): ?bool
    {
        return $this->unemplBenf;
    }

    public function setUnemplBenf(?bool $unemplBenf): self
    {
        $this->unemplBenf = $unemplBenf;

        return $this;
    }

    public function getAsylumSeekerAlw(): ?bool
    {
        return $this->asylumSeekerAlw;
    }

    public function setAsylumSeekerAlw(?bool $asylumSeekerAlw): self
    {
        $this->asylumSeekerAlw = $asylumSeekerAlw;

        return $this;
    }

    public function getTempWaitingAlw(): ?bool
    {
        return $this->tempWaitingAlw;
    }

    public function setTempWaitingAlw(?bool $tempWaitingAlw): self
    {
        $this->tempWaitingAlw = $tempWaitingAlw;

        return $this;
    }

    public function getFamilyAlw(): ?bool
    {
        return $this->familyAlw;
    }

    public function setFamilyAlw(?bool $familyAlw): self
    {
        $this->familyAlw = $familyAlw;

        return $this;
    }

    public function getSolidarityAlw(): ?bool
    {
        return $this->solidarityAlw;
    }

    public function setSolidarityAlw(?bool $solidarityAlw): self
    {
        $this->solidarityAlw = $solidarityAlw;

        return $this;
    }

    public function getPaidTraining(): ?bool
    {
        return $this->paidTraining;
    }

    public function setPaidTraining(?bool $paidTraining): self
    {
        $this->paidTraining = $paidTraining;

        return $this;
    }

    public function getYouthGuarantee(): ?bool
    {
        return $this->youthGuarantee;
    }

    public function setYouthGuarantee(?bool $youthGuarantee): self
    {
        $this->youthGuarantee = $youthGuarantee;

        return $this;
    }

    public function getMaintenance(): ?bool
    {
        return $this->maintenance;
    }

    public function setMaintenance(?bool $maintenance): self
    {
        $this->maintenance = $maintenance;

        return $this;
    }

    public function getActivityBonus(): ?bool
    {
        return $this->activityBonus;
    }

    public function setActivityBonus(?bool $activityBonus): self
    {
        $this->activityBonus = $activityBonus;

        return $this;
    }

    public function getPensionBenf(): ?bool
    {
        return $this->pensionBenf;
    }

    public function setPensionBenf(?bool $pensionBenf): self
    {
        $this->pensionBenf = $pensionBenf;

        return $this;
    }

    public function getMinIncome(): ?bool
    {
        return $this->minIncome;
    }

    public function setMinIncome(?bool $minIncome): self
    {
        $this->minIncome = $minIncome;

        return $this;
    }

    public function getSalary(): ?bool
    {
        return $this->salary;
    }

    public function setSalary(?bool $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getRessourceOther(): ?bool
    {
        return $this->ressourceOther;
    }

    public function setRessourceOther(?bool $ressourceOther): self
    {
        $this->ressourceOther = $ressourceOther;

        return $this;
    }

    public function getRessourceOtherPrecision(): ?string
    {
        return $this->ressourceOtherPrecision;
    }

    public function setRessourceOtherPrecision(?string $ressourceOtherPrecision): self
    {
        $this->ressourceOtherPrecision = $ressourceOtherPrecision;

        return $this;
    }

    public function getDisAdultAlwAmt(): ?float
    {
        return $this->disAdultAlwAmt;
    }

    public function setDisAdultAlwAmt(?float $disAdultAlwAmt): self
    {
        $this->disAdultAlwAmt = $disAdultAlwAmt;

        return $this;
    }

    public function getDisChildAlwAmt(): ?float
    {
        return $this->disChildAlwAmt;
    }

    public function setDisChildAlwAmt(?float $disChildAlwAmt): self
    {
        $this->disChildAlwAmt = $disChildAlwAmt;

        return $this;
    }

    public function getUnemplBenfAmt(): ?float
    {
        return $this->unemplBenfAmt;
    }

    public function setUnemplBenfAmt(?float $unemplBenfAmt): self
    {
        $this->unemplBenfAmt = $unemplBenfAmt;

        return $this;
    }

    public function getAsylumSeekerAlwAmt(): ?float
    {
        return $this->asylumSeekerAlwAmt;
    }

    public function setAsylumSeekerAlwAmt(?float $asylumSeekerAlwAmt): self
    {
        $this->asylumSeekerAlwAmt = $asylumSeekerAlwAmt;

        return $this;
    }

    public function getTempWaitingAlwAmt(): ?float
    {
        return $this->tempWaitingAlwAmt;
    }

    public function setTempWaitingAlwAmt(?float $tempWaitingAlwAmt): self
    {
        $this->tempWaitingAlwAmt = $tempWaitingAlwAmt;

        return $this;
    }

    public function getFamilyAlwAmt(): ?float
    {
        return $this->familyAlwAmt;
    }

    public function setFamilyAlwAmt(?float $familyAlwAmt): self
    {
        $this->familyAlwAmt = $familyAlwAmt;

        return $this;
    }

    public function getSolidarityAlwAmt(): ?float
    {
        return $this->solidarityAlwAmt;
    }

    public function setSolidarityAlwAmt(?float $solidarityAlwAmt): self
    {
        $this->solidarityAlwAmt = $solidarityAlwAmt;

        return $this;
    }

    public function getPaidTrainingAmt(): ?float
    {
        return $this->paidTrainingAmt;
    }

    public function setPaidTrainingAmt(?float $paidTrainingAmt): self
    {
        $this->paidTrainingAmt = $paidTrainingAmt;

        return $this;
    }

    public function getYouthGuaranteeAmt(): ?float
    {
        return $this->youthGuaranteeAmt;
    }

    public function setYouthGuaranteeAmt(?float $youthGuaranteeAmt): self
    {
        $this->youthGuaranteeAmt = $youthGuaranteeAmt;

        return $this;
    }

    public function getMaintenanceAmt(): ?float
    {
        return $this->maintenanceAmt;
    }

    public function setMaintenanceAmt(?float $maintenanceAmt): self
    {
        $this->maintenanceAmt = $maintenanceAmt;

        return $this;
    }

    public function getActivityBonusAmt(): ?float
    {
        return $this->activityBonusAmt;
    }

    public function setActivityBonusAmt(?float $activityBonusAmt): self
    {
        $this->activityBonusAmt = $activityBonusAmt;

        return $this;
    }

    public function getPensionBenfAmt(): ?float
    {
        return $this->pensionBenfAmt;
    }

    public function setPensionBenfAmt(?float $pensionBenfAmt): self
    {
        $this->pensionBenfAmt = $pensionBenfAmt;

        return $this;
    }

    public function getMinIncomeAmt(): ?float
    {
        return $this->minIncomeAmt;
    }

    public function setMinIncomeAmt(?float $minIncomeAmt): self
    {
        $this->minIncomeAmt = $minIncomeAmt;

        return $this;
    }

    public function getSalaryAmt(): ?float
    {
        return $this->salaryAmt;
    }

    public function setSalaryAmt(?float $salaryAmt): self
    {
        $this->salaryAmt = $salaryAmt;

        return $this;
    }

    public function getRessourceOtherAmt(): ?float
    {
        return $this->ressourceOtherAmt;
    }

    public function setRessourceOtherAmt(?float $ressourceOtherAmt): self
    {
        $this->ressourceOtherAmt = $ressourceOtherAmt;

        return $this;
    }

    public function getTaxIncomeN1(): ?float
    {
        return $this->taxIncomeN1;
    }

    public function setTaxIncomeN1(?float $taxIncomeN1): self
    {
        $this->taxIncomeN1 = $taxIncomeN1;

        return $this;
    }

    public function getTaxIncomeN2(): ?float
    {
        return $this->taxIncomeN2;
    }

    public function setTaxIncomeN2(?float $taxIncomeN2): self
    {
        $this->taxIncomeN2 = $taxIncomeN2;

        return $this;
    }

    public function getRessourcesComment(): ?string
    {
        return $this->ressourcesComment;
    }

    public function setRessourcesComment(?string $ressourcesComment): self
    {
        $this->ressourcesComment = $ressourcesComment;

        return $this;
    }

    public function getCharges(): ?float
    {
        return $this->charges;
    }

    public function setCharges(?float $charges): self
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

    public function getRent(): ?bool
    {
        return $this->rent;
    }

    public function setRent(?bool $rent): self
    {
        $this->rent = $rent;

        return $this;
    }

    public function getElectricityGas(): ?bool
    {
        return $this->electricityGas;
    }

    public function setElectricityGas(?bool $electricityGas): self
    {
        $this->electricityGas = $electricityGas;

        return $this;
    }

    public function getWater(): ?bool
    {
        return $this->water;
    }

    public function setWater(?bool $water): self
    {
        $this->water = $water;

        return $this;
    }

    public function getInsurance(): ?bool
    {
        return $this->insurance;
    }

    public function setInsurance(?bool $insurance): self
    {
        $this->insurance = $insurance;

        return $this;
    }

    public function getMutual(): ?bool
    {
        return $this->mutual;
    }

    public function setMutual(?bool $mutual): self
    {
        $this->mutual = $mutual;

        return $this;
    }

    public function getTaxes(): ?bool
    {
        return $this->taxes;
    }

    public function setTaxes(?bool $taxes): self
    {
        $this->taxes = $taxes;

        return $this;
    }

    public function getTransport(): ?bool
    {
        return $this->transport;
    }

    public function setTransport(?bool $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getChildcare(): ?bool
    {
        return $this->childcare;
    }

    public function setChildcare(?bool $childcare): self
    {
        $this->childcare = $childcare;

        return $this;
    }

    public function getAlimony(): ?bool
    {
        return $this->alimony;
    }

    public function setAlimony(?bool $alimony): self
    {
        $this->alimony = $alimony;

        return $this;
    }

    public function getPhone(): ?bool
    {
        return $this->phone;
    }

    public function setPhone(?bool $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getChargeOther(): ?bool
    {
        return $this->chargeOther;
    }

    public function setChargeOther(?bool $chargeOther): self
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

    public function getDebts(): ?float
    {
        return $this->debts;
    }

    public function setDebts(?float $debts): self
    {
        $this->debts = $debts;

        return $this;
    }

    public function getDebtRental(): ?bool
    {
        return $this->debtRental;
    }

    public function setDebtRental(?bool $debtRental): self
    {
        $this->debtRental = $debtRental;

        return $this;
    }

    public function getDebtConsrCredit(): ?bool
    {
        return $this->debtConsrCredit;
    }

    public function setDebtConsrCredit(?bool $debtConsrCredit): self
    {
        $this->debtConsrCredit = $debtConsrCredit;

        return $this;
    }

    public function getDebtMortgage(): ?bool
    {
        return $this->debtMortgage;
    }

    public function setDebtMortgage(bool $debtMortgage): self
    {
        $this->debtMortgage = $debtMortgage;

        return $this;
    }

    public function getDebtFines(): ?bool
    {
        return $this->debtFines;
    }

    public function setDebtFines(?bool $debtFines): self
    {
        $this->debtFines = $debtFines;

        return $this;
    }

    public function getDebtTaxDelays(): ?bool
    {
        return $this->debtTaxDelays;
    }

    public function setDebtTaxDelays(?bool $debtTaxDelays): self
    {
        $this->debtTaxDelays = $debtTaxDelays;

        return $this;
    }

    public function getDebtBankOverdrafts(): ?bool
    {
        return $this->debtBankOverdrafts;
    }

    public function setDebtBankOverdrafts(?bool $debtBankOverdrafts): self
    {
        $this->debtBankOverdrafts = $debtBankOverdrafts;

        return $this;
    }

    public function getDebtOther(): ?bool
    {
        return $this->debtOther;
    }

    public function setDebtOther(?bool $debtOther): self
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

    public function getOverIndebtRecord(): ?float
    {
        return $this->overIndebtRecord;
    }

    public function setOverIndebtRecord(?float $overIndebtRecord): self
    {
        $this->overIndebtRecord = $overIndebtRecord;

        return $this;
    }

    public function getOverIndebtRecordList()
    {
        return self::OVER_INDEBT_RECCORD[$this->overIndebtRecord];
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

    public function getSettlementPlan(): ?float
    {
        return $this->settlementPlan;
    }

    public function setSettlementPlan(?float $settlementPlan): self
    {
        $this->settlementPlan = $settlementPlan;

        return $this;
    }

    public function getSettlementPlanList()
    {
        return self::SETTLEMENT_PLAN[$this->settlementPlan];
    }

    public function getMoratorium(): ?float
    {
        return $this->moratorium;
    }

    public function setMoratorium(?float $moratorium): self
    {
        $this->moratorium = $moratorium;

        return $this;
    }

    public function getCommentSitBudget(): ?string
    {
        return $this->commentSitBudget;
    }

    public function setCommentSitBudget(?string $commentSitBudget): self
    {
        $this->commentSitBudget = $commentSitBudget;

        return $this;
    }

    public function getSupportPerson(): ?Supportpers
    {
        return $this->supportPerson;
    }

    public function setSupportPerson(Supportperson $supportPerson): self
    {
        $this->supportPerson = $supportPerson;

        return $this;
    }
}
