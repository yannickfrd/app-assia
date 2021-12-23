<?php

namespace App\Entity\Traits;

use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\Resource;
use App\Form\Utils\Choices;

trait ResourcesEntityTrait
{
    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $resource;

    /** @Groups("export") */
    private $resourceToString;

    /** @Groups("export") */
    private $resourcesToString;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $resourcesAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $salaryAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $unemplBenefitAmt; // ARE

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $dailyAllowanceAmt; // IJ

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $minimumIncomeAmt; // RSA

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $familyAllowanceAmt; // AF

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $unemplBenefit; // ARE

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $disAdultAllowance; // AAH

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $disChildAllowance; // AEEH

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $asylumAllowance; // ADA

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $tempWaitingAllowance; // ATA

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $familyAllowance; // AF

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $solidarityAllowance; // ASS

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $paidTraining; // Formation

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $youthGuarantee; // Garantie Jeunes

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $maintenance; // Pension alimentaire

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $activityBonus; // Prime d'activité

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $pensionBenefit; // Retraite

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $minimumIncome; // RSA

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
    private $familySupplement;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $scholarships;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $dailyAllowance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $ressourceOther;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ressourceOtherPrecision;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $disAdultAllowanceAmt; //AAH

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $disChildAllowanceAmt; // AEEH

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $asylumAllowanceAmt; // ADA

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tempWaitingAllowanceAmt; // ATA

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $familySupplementAmt; // Complément familial

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $scholarshipsAmt; // Bourse

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $solidarityAllowanceAmt; // ASS

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $paidTrainingAmt; // Formation

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $youthGuaranteeAmt; // Garantie Jeunes

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maintenanceAmt; // Pension alimentaire

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $activityBonusAmt; // Prime d'activité

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pensionBenefitAmt; // Retraite

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pajeAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $asfAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $disabilityPensionAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ressourceOtherAmt;

    public function getResource(): ?int
    {
        return $this->resource;
    }

    public function getResourceToString(): ?string
    {
        return $this->resource ? EvalBudgetPerson::RESOURCES[$this->resource] : null;
    }

    public function setResource(?int $resource): self
    {
        $this->resource = $resource;

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

    public function getDisabilityPensionToString(): ?string
    {
        return $this->disabilityPension ? Choices::YES_NO_BOOLEAN[$this->disabilityPension] : null;
    }

    public function setDisabilityPension(?int $disabilityPension): self
    {
        $this->disabilityPension = $disabilityPension;

        return $this;
    }

    public function getFamilySupplement(): ?int
    {
        return $this->familySupplement;
    }

    public function getFamilySupplementToString(): ?string
    {
        return $this->familySupplement ? Choices::YES_NO_BOOLEAN[$this->familySupplement] : null;
    }

    public function setFamilySupplement(?int $familySupplement): self
    {
        $this->familySupplement = $familySupplement;

        return $this;
    }

    public function getScholarships(): ?int
    {
        return $this->scholarships;
    }

    public function getScholarshipsToString(): ?string
    {
        return $this->scholarships ? Choices::YES_NO_BOOLEAN[$this->scholarships] : null;
    }

    public function setScholarships(?int $scholarships): self
    {
        $this->scholarships = $scholarships;

        return $this;
    }

    public function getYouthGuarantee(): ?int
    {
        return $this->youthGuarantee;
    }

    public function getYouthGuaranteeToString(): ?string
    {
        return $this->youthGuarantee ? Choices::YES_NO_BOOLEAN[$this->youthGuarantee] : null;
    }

    public function setYouthGuarantee(?int $youthGuarantee): self
    {
        $this->youthGuarantee = $youthGuarantee;

        return $this;
    }

    public function getDailyAllowance(): ?int
    {
        return $this->dailyAllowance;
    }

    public function getDailyAllowanceToString(): ?string
    {
        return $this->dailyAllowance ? Choices::YES_NO_BOOLEAN[$this->dailyAllowance] : null;
    }

    public function setDailyAllowance(?int $dailyAllowance): self
    {
        $this->dailyAllowance = $dailyAllowance;

        return $this;
    }

    public function getMaintenance(): ?int
    {
        return $this->maintenance;
    }

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
        return $this->findOneResourceAmt(Resource::ARE);
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
        return $this->findOneResourceAmt(Resource::AF);
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
        return $this->findOneResourceAmt(Resource::RSA);
    }

    public function setMinimumIncomeAmt(?float $minimumIncomeAmt): self
    {
        $this->minimumIncomeAmt = $minimumIncomeAmt;

        return $this;
    }

    public function getSalaryAmt(): ?float
    {
        return $this->findOneResourceAmt(Resource::SALARY);
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

    public function getFamilySupplementAmt(): ?float
    {
        return $this->familySupplementAmt;
    }

    public function setFamilySupplementAmt(?float $familySupplementAmt): self
    {
        $this->familySupplementAmt = $familySupplementAmt;

        return $this;
    }

    public function getScholarshipsAmt(): ?float
    {
        return $this->scholarshipsAmt;
    }

    public function setScholarshipsAmt(?float $scholarshipsAmt): self
    {
        $this->scholarshipsAmt = $scholarshipsAmt;

        return $this;
    }

    public function getDailyAllowanceAmt(): ?float
    {
        return $this->findOneResourceAmt(Resource::IJ);
    }

    public function setDailyAllowanceAmt(?float $dailyAllowanceAmt): self
    {
        $this->dailyAllowanceAmt = $dailyAllowanceAmt;

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

    public function getResourcesToArray(): array
    {
        if (!$this->resources) {
            return [];
        }

        $resources = [];

        foreach ($this->resources as $resource) {
            $resources[] = Resource::RESOURCES[$resource->getType()].
                (Resource::OTHER === $resource->getType() && $resource->getComment() ? ' ('.$resource->getComment().')' : '');
        }

        return $resources;
    }

    public function getResourcesToString(): ?string
    {
        return join(', ', $this->getResourcesToArray());
    }

    /**
     * @param array|int $types
     */
    protected function findOneResourceAmt($values): float
    {
        $amount = 0;

        if (!is_array($values)) {
            $types = [];
            $types[] = $values;
        } else {
            $types = $values;
        }

        foreach ($this->resources as $resource) {
            if (in_array($resource->getType(), $types)) {
                $amount += $resource->getAmount();
            }
        }

        return $amount;
    }
}
