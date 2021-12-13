<?php

namespace App\Entity\Evaluation;

use App\Entity\Traits\ResourcesEntityTrait;
use App\Form\Utils\EvaluationChoices;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalBudgePersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalBudgetPerson
{
    use ResourcesEntityTrait;
    use SoftDeleteableEntity;

    public const RESOURCES = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Démarches en cours',
        4 => 'Droits supendus',
        99 => 'Non évalué',
    ];

    public const RESOURCES_TYPE = [
        'salary' => 'Salaire',
        'activityBonus' => 'Prime d\'activité',
        'unemplBenefit' => 'Allocation chômage (ARE)',
        'minimumIncome' => 'RSA',
        'familyAllowance' => 'Prestations familiales (AF)',
        'disAdultAllowance' => 'Allocation adulte handicapé (AAH)',
        'disChildAllowance' => 'Allocation d\'éducation de l\'enfant handicapé (AEEH)',
        'asf' => 'Allocation de soutien familial (ASF)',
        'solidarityAllowance' => 'Allocation de solidarité spécifique (ASS)',
        'asylumAllowance' => 'Allocation pour demandeur d\'asile (ADA)',
        // 'tempWaitingAllowance' => 'Allocation temporaire d\'attente (ATA)', // To delete
        'scholarships' => 'Bourse',
        'familySupplement' => 'Complément familial',
        'paidTraining' => 'Formation rémunérée',
        'youthGuarantee' => 'Garantie jeunes',
        'dailyAllowance' => 'Indemnités journalières (IJ)',
        'maintenance' => 'Pension alimentaire',
        'disabilityPension' => 'Pension d\'invalidité',
        'paje' => 'Prestation d\'accueil du jeune enfant (PAJE)',
        'pensionBenefit' => 'Retraite',
        'ressourceOther' => 'Autre ressource',
    ];

    public const RESOURCES_MIN_TYPE = [
        'salary' => 'Salaire',
        'activityBonus' => 'Prime d\'activité',
        'unemplBenefit' => 'ARE',
        'minimumIncome' => 'RSA',
        'familyAllowance' => 'AF',
        'disAdultAllowance' => 'AAH',
        'disChildAllowance' => 'AEEH',
        'asf' => 'ASF',
        'solidarityAllowance' => 'ASS',
        'asylumAllowance' => 'ADA',
        // 'tempWaitingAllowance' => 'ATA', // To delete
        'scholarships' => 'Bourse',
        'familySupplement' => 'Complément familial',
        'paidTraining' => 'Formation',
        'youthGuarantee' => 'Garantie jeunes',
        'dailyAllowance' => 'IJ',
        'maintenance' => 'Pension alimentaire',
        'disabilityPension' => 'Pension d\'invalidité',
        'paje' => 'PAJE',
        'pensionBenefit' => 'Retraite',
        'ressourceOther' => 'Autre',
    ];

    public const CHARGES_TYPE = [
        'insurance' => 'Assurance(s)',
        'canteen' => 'Cantine',
        'consumerCredit' => 'Crédit(s) à la consommation',
        'water' => 'Eau',
        'electricityGas' => 'Electricité / Gaz',
        'childcare' => 'Garde d\'enfant(s)',
        'taxes' => 'Impôts',
        'rent' => 'Loyer',
        'mutual' => 'Mutuelle(s)',
        'alimony' => 'Pension alimentaire',
        'phone' => 'Téléphone',
        'transport' => 'Transport',
        'chargeOther' => 'Autre charge',
    ];

    public const DEBTS_TYPE = [
        'debtRental' => 'Dettes locatives',
        'debtConsrCredit' => 'Dette de crédits à la consommation',
        'debtMortgage' => 'Dettes de crédits immobiliers',
        'debtFines' => 'Amendes',
        'debtHealth' => 'Dettes de santé (hôpital)',
        'debtTaxDelays' => 'Retards d\'impôts',
        'debtBankOverdrafts' => 'Découverts bancaires',
        'debtOther' => 'Autres dettes',
    ];

    public const SETTLEMENT_PLAN = [
        1 => 'Proposé',
        2 => 'Accepté',
        3 => 'Refusé',
        4 => 'En cours',
        99 => 'Non évalué',
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
    private $incomeTax;

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
    private $consumerCredit;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $canteen;

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
    private $consumerCreditAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $canteenAmt;

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
    private $debtHealth;

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
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvaluationPerson", inversedBy="evalBudgetPerson", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $evaluationPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIncomeTax(): ?int
    {
        return $this->incomeTax;
    }

    /**
     * @Groups("export")
     */
    public function getIncomeTaxToString(): ?string
    {
        return $this->incomeTax ? EvaluationChoices::YES_NO[$this->incomeTax] : null;
    }

    public function setIncomeTax(?int $incomeTax): self
    {
        $this->incomeTax = $incomeTax;

        return $this;
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
        return $this->charges ? EvaluationChoices::YES_NO[$this->charges] : null;
    }

    public function setCharges(?int $charges): self
    {
        $this->charges = $charges;

        return $this;
    }

    public function chargesTypes(): array
    {
        $array = [];

        foreach (EvalBudgetPerson::CHARGES_TYPE as $key => $value) {
            $method = 'get'.ucfirst($key);
            if (EvaluationChoices::YES === $this->$method()) {
                $array[$key] = $value;
            }
        }

        if ($this->chargeOther && $this->chargeOtherPrecision) {
            $array['chargeOther'] = $array['chargeOther'].' ('.$this->chargeOtherPrecision.')';
        }

        return $array;
    }

    public function getChargesTypesToString(): ?string
    {
        return join(', ', $this->chargesTypes());
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
        return $this->electricityGas ? EvaluationChoices::YES_NO_BOOLEAN[$this->electricityGas] : null;
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
        return $this->water ? EvaluationChoices::YES_NO_BOOLEAN[$this->water] : null;
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
        return $this->insurance ? EvaluationChoices::YES_NO_BOOLEAN[$this->insurance] : null;
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
        return $this->mutual ? EvaluationChoices::YES_NO_BOOLEAN[$this->mutual] : null;
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
        return $this->taxes ? EvaluationChoices::YES_NO_BOOLEAN[$this->taxes] : null;
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
        return $this->transport ? EvaluationChoices::YES_NO_BOOLEAN[$this->transport] : null;
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
        return $this->childcare ? EvaluationChoices::YES_NO_BOOLEAN[$this->childcare] : null;
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
        return $this->alimony ? EvaluationChoices::YES_NO_BOOLEAN[$this->alimony] : null;
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
        return $this->phone ? EvaluationChoices::YES_NO_BOOLEAN[$this->phone] : null;
    }

    public function setPhone(?int $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getConsumerCredit(): ?int
    {
        return $this->consumerCredit;
    }

    public function getConsumerCreditToString(): ?string
    {
        return $this->consumerCredit ? EvaluationChoices::YES_NO_BOOLEAN[$this->consumerCredit] : null;
    }

    public function setConsumerCredit(?int $consumerCredit): self
    {
        $this->consumerCredit = $consumerCredit;

        return $this;
    }

    public function getCanteen(): ?int
    {
        return $this->canteen;
    }

    public function getCanteenToString(): ?string
    {
        return $this->canteen ? EvaluationChoices::YES_NO_BOOLEAN[$this->canteen] : null;
    }

    public function setCanteen(?int $canteen): self
    {
        $this->canteen = $canteen;

        return $this;
    }

    public function getChargeOther(): ?int
    {
        return $this->chargeOther;
    }

    public function getChargeOtherToString(): ?string
    {
        return $this->chargeOther ? EvaluationChoices::YES_NO_BOOLEAN[$this->chargeOther] : null;
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

    public function getConsumerCreditAmt(): ?float
    {
        return $this->consumerCreditAmt;
    }

    public function setConsumerCreditAmt(?float $consumerCreditAmt): self
    {
        $this->consumerCreditAmt = $consumerCreditAmt;

        return $this;
    }

    public function getCanteenAmt(): ?float
    {
        return $this->canteenAmt;
    }

    public function setCanteenAmt(?float $canteenAmt): self
    {
        $this->canteenAmt = $canteenAmt;

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

    public function debtsTypes(): array
    {
        $array = [];

        foreach (self::DEBTS_TYPE as $key => $value) {
            $method = 'get'.ucfirst($key);
            if (EvaluationChoices::YES === $this->$method()) {
                $array[$key] = $value;
            }
        }

        if ($this->debtOther && $this->debtOtherPrecision) {
            $array['debtOther'] = $array['debtOther'].' ('.$this->debtOtherPrecision.')';
        }

        return $array;
    }

    public function getDebtsTypesToString(): ?string
    {
        return join(', ', $this->debtsTypes());
    }

    /**
     * @Groups("export")
     */
    public function getDebtsToString(): ?string
    {
        return $this->debts ? EvaluationChoices::YES_NO[$this->debts] : null;
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
        return $this->debtRental ? EvaluationChoices::YES_NO_BOOLEAN[$this->debtRental] : null;
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
        return $this->debtConsrCredit ? EvaluationChoices::YES_NO_BOOLEAN[$this->debtConsrCredit] : null;
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
        return $this->debtMortgage ? EvaluationChoices::YES_NO_BOOLEAN[$this->debtMortgage] : null;
    }

    public function setDebtMortgage(?int $debtMortgage): self
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
        return $this->debtFines ? EvaluationChoices::YES_NO_BOOLEAN[$this->debtFines] : null;
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
        return $this->debtTaxDelays ? EvaluationChoices::YES_NO_BOOLEAN[$this->debtTaxDelays] : null;
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
        return $this->getDebtBankOverdrafts() ? EvaluationChoices::YES_NO_BOOLEAN[$this->debtBankOverdrafts] : null;
    }

    public function setDebtBankOverdrafts(?int $debtBankOverdrafts): self
    {
        $this->debtBankOverdrafts = $debtBankOverdrafts;

        return $this;
    }

    public function getDebtHealth(): ?int
    {
        return $this->debtHealth;
    }

    public function getDebtHealthToString(): ?string
    {
        return $this->debtHealth ? EvaluationChoices::YES_NO_BOOLEAN[$this->debtHealth] : null;
    }

    public function setDebtHealth(?int $debtHealth): self
    {
        $this->debtHealth = $debtHealth;

        return $this;
    }

    public function getDebtOther(): ?int
    {
        return $this->debtOther;
    }

    public function getDebtOtherToString(): ?string
    {
        return $this->debtOther ? EvaluationChoices::YES_NO_BOOLEAN[$this->debtOther] : null;
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
        return $this->overIndebtRecord ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->overIndebtRecord] : null;
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
        return $this->moratorium ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->moratorium] : null;
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
