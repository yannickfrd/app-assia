<?php

namespace App\Entity\Traits;

use App\Entity\Evaluation\EvalBudgetPerson;
use App\Form\Utils\Choices;

trait ResourcesEntityTrait
{
    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $resources;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $resourcesAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $disAdultAllowance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $disChildAllowance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $unemplBenefit;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $asylumAllowance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $tempWaitingAllowance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $familyAllowance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $solidarityAllowance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $paidTraining;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $youthGuarantee;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $maintenance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $activityBonus;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $pensionBenefit;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $minimumIncome;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $salary;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $paje;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $asf;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $disabilityPension;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $ressourceOther;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("export")
     */
    private $ressourceOtherPrecision;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $disAdultAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $disChildAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $unemplBenefitAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $asylumAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $tempWaitingAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $familyAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $solidarityAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $paidTrainingAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $youthGuaranteeAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $maintenanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $activityBonusAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $pensionBenefitAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $minimumIncomeAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $salaryAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $pajeAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $asfAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $disabilityPensionAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $ressourceOtherAmt;

    public function getResources(): ?int
    {
        return $this->resources;
    }

    /**
     * @Groups("export")
     */
    public function getResourcesToString(): ?string
    {
        return $this->resources ? EvalBudgetPerson::RESOURCES[$this->resources] : null;
    }

    public function setResources(?int $resources): self
    {
        $this->resources = $resources;

        return $this;
    }

    public function getResourcesAmt(): ?float
    {
        return $this->resourcesAmt;
    }

    public function setResourcesAmt(?float $resourcesAmt): self
    {
        $this->resourcesAmt = $resourcesAmt;

        return $this;
    }

    public function getDisAdultAllowance(): ?int
    {
        return $this->disAdultAllowance;
    }

    /**
     * @Groups("export")
     */
    public function getDisAdultAllowanceToString(): ?string
    {
        return $this->disAdultAllowance ? Choices::YES_NO_BOOLEAN[$this->disAdultAllowance] : null;
    }

    public function setDisAdultAllowance(?int $disAdultAllowance): self
    {
        $this->disAdultAllowance = $disAdultAllowance;

        return $this;
    }

    public function getDisChildAllowance(): ?int
    {
        return $this->disChildAllowance;
    }

    /**
     * @Groups("export")
     */
    public function getDisChildAllowanceToString(): ?string
    {
        return $this->disChildAllowance ? Choices::YES_NO_BOOLEAN[$this->disChildAllowance] : null;
    }

    public function setDisChildAllowance(?int $disChildAllowance): self
    {
        $this->disChildAllowance = $disChildAllowance;

        return $this;
    }

    public function getUnemplBenefit(): ?int
    {
        return $this->unemplBenefit;
    }

    /**
     * @Groups("export")
     */
    public function getUnemplBenefitToString(): ?string
    {
        return $this->unemplBenefit ? Choices::YES_NO_BOOLEAN[$this->unemplBenefit] : null;
    }

    public function setUnemplBenefit(?int $unemplBenefit): self
    {
        $this->unemplBenefit = $unemplBenefit;

        return $this;
    }

    public function getAsylumAllowance(): ?int
    {
        return $this->asylumAllowance;
    }

    /**
     * @Groups("export")
     */
    public function getAsylumAllowanceToString(): ?string
    {
        return $this->asylumAllowance ? Choices::YES_NO_BOOLEAN[$this->asylumAllowance] : null;
    }

    public function setAsylumAllowance(?int $asylumAllowance): self
    {
        $this->asylumAllowance = $asylumAllowance;

        return $this;
    }

    public function getTempWaitingAllowance(): ?int
    {
        return $this->tempWaitingAllowance;
    }

    /**
     * @Groups("export")
     */
    public function getTempWaitingAllowanceToString(): ?string
    {
        return $this->tempWaitingAllowance ? Choices::YES_NO_BOOLEAN[$this->tempWaitingAllowance] : null;
    }

    public function setTempWaitingAllowance(?int $tempWaitingAllowance): self
    {
        $this->tempWaitingAllowance = $tempWaitingAllowance;

        return $this;
    }

    public function getFamilyAllowance(): ?int
    {
        return $this->familyAllowance;
    }

    /**
     * @Groups("export")
     */
    public function getFamilyAllowanceToString(): ?string
    {
        return $this->getFamilyAllowance() ? Choices::YES_NO_BOOLEAN[$this->familyAllowance] : null;
    }

    public function setFamilyAllowance(?int $familyAllowance): self
    {
        $this->familyAllowance = $familyAllowance;

        return $this;
    }

    public function getSolidarityAllowance(): ?int
    {
        return $this->solidarityAllowance;
    }

    /**
     * @Groups("export")
     */
    public function getSolidarityAllowanceToString(): ?string
    {
        return $this->solidarityAllowance ? Choices::YES_NO_BOOLEAN[$this->solidarityAllowance] : null;
    }

    public function setSolidarityAllowance(?int $solidarityAllowance): self
    {
        $this->solidarityAllowance = $solidarityAllowance;

        return $this;
    }

    public function getPaidTraining(): ?int
    {
        return $this->paidTraining;
    }

    /**
     * @Groups("export")
     */
    public function getPaidTrainingToString(): ?string
    {
        return $this->paidTraining ? Choices::YES_NO_BOOLEAN[$this->paidTraining] : null;
    }

    public function setPaidTraining(?int $paidTraining): self
    {
        $this->paidTraining = $paidTraining;

        return $this;
    }

    public function getPaje(): ?int
    {
        return $this->paje;
    }

    /**
     * @Groups("export")
     */
    public function getPajeToString(): ?string
    {
        return $this->paje ? Choices::YES_NO_BOOLEAN[$this->paje] : null;
    }

    public function setPaje(?int $paje): self
    {
        $this->paje = $paje;

        return $this;
    }

    public function getAsf(): ?int
    {
        return $this->asf;
    }

    /**
     * @Groups("export")
     */
    public function getAsfToString(): ?string
    {
        return $this->asf ? Choices::YES_NO_BOOLEAN[$this->asf] : null;
    }

    public function setAsf(?int $asf): self
    {
        $this->asf = $asf;

        return $this;
    }

    public function getDisabilityPension(): ?int
    {
        return $this->disabilityPension;
    }

    /**
     * @Groups("export")
     */
    public function getDisabilityPensionToString(): ?string
    {
        return $this->disabilityPension ? Choices::YES_NO_BOOLEAN[$this->disabilityPension] : null;
    }

    public function setDisabilityPension(?int $disabilityPension): self
    {
        $this->disabilityPension = $disabilityPension;

        return $this;
    }

    public function getYouthGuarantee(): ?int
    {
        return $this->youthGuarantee;
    }

    /**
     * @Groups("export")
     */
    public function getYouthGuaranteeToString(): ?string
    {
        return $this->youthGuarantee ? Choices::YES_NO_BOOLEAN[$this->youthGuarantee] : null;
    }

    public function setYouthGuarantee(?int $youthGuarantee): self
    {
        $this->youthGuarantee = $youthGuarantee;

        return $this;
    }

    public function getMaintenance(): ?int
    {
        return $this->maintenance;
    }

    /**
     * @Groups("export")
     */
    public function getMaintenanceToString(): ?string
    {
        return $this->maintenance ? Choices::YES_NO_BOOLEAN[$this->maintenance] : null;
    }

    public function setMaintenance(?int $maintenance): self
    {
        $this->maintenance = $maintenance;

        return $this;
    }

    public function getActivityBonus(): ?int
    {
        return $this->activityBonus;
    }

    /**
     * @Groups("export")
     */
    public function getActivityBonusToString(): ?string
    {
        return $this->activityBonus ? Choices::YES_NO_BOOLEAN[$this->activityBonus] : null;
    }

    public function setActivityBonus(?int $activityBonus): self
    {
        $this->activityBonus = $activityBonus;

        return $this;
    }

    public function getPensionBenefit(): ?int
    {
        return $this->pensionBenefit;
    }

    /**
     * @Groups("export")
     */
    public function getPensionBenefitToString(): ?string
    {
        return $this->pensionBenefit ? Choices::YES_NO_BOOLEAN[$this->pensionBenefit] : null;
    }

    public function setPensionBenefit(?int $pensionBenefit): self
    {
        $this->pensionBenefit = $pensionBenefit;

        return $this;
    }

    public function getMinimumIncome(): ?int
    {
        return $this->minimumIncome;
    }

    /**
     * @Groups("export")
     */
    public function getMinimumIncomeToString(): ?string
    {
        return $this->minimumIncome ? Choices::YES_NO_BOOLEAN[$this->minimumIncome] : null;
    }

    public function setMinimumIncome(?int $minimumIncome): self
    {
        $this->minimumIncome = $minimumIncome;

        return $this;
    }

    public function getSalary(): ?int
    {
        return $this->salary;
    }

    /**
     * @Groups("export")
     */
    public function getSalaryToString(): ?string
    {
        return $this->salary ? Choices::YES_NO_BOOLEAN[$this->salary] : null;
    }

    public function setSalary(?int $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getRessourceOther(): ?int
    {
        return $this->ressourceOther;
    }

    /**
     * @Groups("export")
     */
    public function getRessourceOtherToString(): ?string
    {
        return $this->ressourceOther ? Choices::YES_NO_BOOLEAN[$this->ressourceOther] : null;
    }

    public function setRessourceOther(?int $ressourceOther): self
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

    public function getDisAdultAllowanceAmt(): ?float
    {
        return $this->disAdultAllowanceAmt;
    }

    public function setDisAdultAllowanceAmt(?float $disAdultAllowanceAmt): self
    {
        $this->disAdultAllowanceAmt = $disAdultAllowanceAmt;

        return $this;
    }

    public function getDisChildAllowanceAmt(): ?float
    {
        return $this->disChildAllowanceAmt;
    }

    public function setDisChildAllowanceAmt(?float $disChildAllowanceAmt): self
    {
        $this->disChildAllowanceAmt = $disChildAllowanceAmt;

        return $this;
    }

    public function getUnemplBenefitAmt(): ?float
    {
        return $this->unemplBenefitAmt;
    }

    public function setUnemplBenefitAmt(?float $unemplBenefitAmt): self
    {
        $this->unemplBenefitAmt = $unemplBenefitAmt;

        return $this;
    }

    public function getAsylumAllowanceAmt(): ?float
    {
        return $this->asylumAllowanceAmt;
    }

    public function setAsylumAllowanceAmt(?float $asylumAllowanceAmt): self
    {
        $this->asylumAllowanceAmt = $asylumAllowanceAmt;

        return $this;
    }

    public function getTempWaitingAllowanceAmt(): ?float
    {
        return $this->tempWaitingAllowanceAmt;
    }

    public function setTempWaitingAllowanceAmt(?float $tempWaitingAllowanceAmt): self
    {
        $this->tempWaitingAllowanceAmt = $tempWaitingAllowanceAmt;

        return $this;
    }

    public function getFamilyAllowanceAmt(): ?float
    {
        return $this->familyAllowanceAmt;
    }

    public function setFamilyAllowanceAmt(?float $familyAllowanceAmt): self
    {
        $this->familyAllowanceAmt = $familyAllowanceAmt;

        return $this;
    }

    public function getSolidarityAllowanceAmt(): ?float
    {
        return $this->solidarityAllowanceAmt;
    }

    public function setSolidarityAllowanceAmt(?float $solidarityAllowanceAmt): self
    {
        $this->solidarityAllowanceAmt = $solidarityAllowanceAmt;

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

    public function getPensionBenefitAmt(): ?float
    {
        return $this->pensionBenefitAmt;
    }

    public function setPensionBenefitAmt(?float $pensionBenefitAmt): self
    {
        $this->pensionBenefitAmt = $pensionBenefitAmt;

        return $this;
    }

    public function getMinimumIncomeAmt(): ?float
    {
        return $this->minimumIncomeAmt;
    }

    public function setMinimumIncomeAmt(?float $minimumIncomeAmt): self
    {
        $this->minimumIncomeAmt = $minimumIncomeAmt;

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

    public function getPajeAmt(): ?float
    {
        return $this->pajeAmt;
    }

    public function setPajeAmt(?float $pajeAmt): self
    {
        $this->pajeAmt = $pajeAmt;

        return $this;
    }

    public function getAsfAmt(): ?float
    {
        return $this->asfAmt;
    }

    public function setAsfAmt(?float $asfAmt): self
    {
        $this->asfAmt = $asfAmt;

        return $this;
    }

    public function getDisabilityPensionAmt(): ?float
    {
        return $this->disabilityPensionAmt;
    }

    public function setDisabilityPensionAmt(?float $disabilityPensionAmt): self
    {
        $this->disabilityPensionAmt = $disabilityPensionAmt;

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

    public function getResourcesType(): array
    {
        $array = [];

        foreach (EvalBudgetPerson::RESOURCES_TYPE as $key => $value) {
                $method = 'get'.ucfirst($key);
            if (Choices::YES === $this->$method()) {
                $array[] = $value;
            }
        }
        
        if ($this->getRessourceOtherPrecision()) {
            $array[] = $this->getRessourceOtherPrecision();
        }

        return $array;
    }
}
