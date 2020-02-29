<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InitEvalPersonRepository")
 */
class InitEvalPerson
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $paperType;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $rightSocialSecurity;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $socialSecurity;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $familyBreakdown;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $friendshipBreakdown;


    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $resources;

    /**
     * @ORM\Column(type="float", nullable=true)
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
    private $ressourceOther;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ressourceOtherPrecision;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $disAdultAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $disChildAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $unemplBenefitAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $asylumAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $tempWaitingAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $familyAllowanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $solidarityAllowanceAmt;

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
    private $pensionBenefitAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minimumIncomeAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $salaryAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $ressourceOtherAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debts;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $debtsAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $profStatus;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $contractType;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportPerson", inversedBy="initEvalPerson", cascade={"persist", "remove"})
     */
    private $supportPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaperType(): ?int
    {
        return $this->paperType;
    }

    public function setPaperType(?int $paperType): self
    {
        $this->paperType = $paperType;

        return $this;
    }

    public function getRightSocialSecurity(): ?int
    {
        return $this->rightSocialSecurity;
    }

    public function setRightSocialSecurity(?int $rightSocialSecurity): self
    {
        $this->rightSocialSecurity = $rightSocialSecurity;

        return $this;
    }

    public function getSocialSecurity(): ?int
    {
        return $this->socialSecurity;
    }

    public function setSocialSecurity(?int $socialSecurity): self
    {
        $this->socialSecurity = $socialSecurity;

        return $this;
    }

    public function getFamilyBreakdown(): ?int
    {
        return $this->familyBreakdown;
    }

    public function setFamilyBreakdown(?int $familyBreakdown): self
    {
        $this->familyBreakdown = $familyBreakdown;

        return $this;
    }

    public function getFriendshipBreakdown(): ?int
    {
        return $this->friendshipBreakdown;
    }

    public function setFriendshipBreakdown(?int $friendshipBreakdown): self
    {
        $this->friendshipBreakdown = $friendshipBreakdown;

        return $this;
    }


    public function getResources(): ?int
    {
        return $this->resources;
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

    public function setDisAdultAllowance(?int $disAdultAllowance): self
    {
        $this->disAdultAllowance = $disAdultAllowance;

        return $this;
    }

    public function getDisChildAllowance(): ?int
    {
        return $this->disChildAllowance;
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

    public function setUnemplBenefit(?int $unemplBenefit): self
    {
        $this->unemplBenefit = $unemplBenefit;

        return $this;
    }

    public function getAsylumAllowance(): ?int
    {
        return $this->asylumAllowance;
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

    public function setTempWaitingAllowance(?int $tempWaitingAllowance): self
    {
        $this->tempWaitingAllowance = $tempWaitingAllowance;

        return $this;
    }

    public function getFamilyAllowance(): ?int
    {
        return $this->familyAllowance;
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

    public function setSolidarityAllowance(?int $solidarityAllowance): self
    {
        $this->solidarityAllowance = $solidarityAllowance;

        return $this;
    }

    public function getPaidTraining(): ?int
    {
        return $this->paidTraining;
    }

    public function setPaidTraining(?int $paidTraining): self
    {
        $this->paidTraining = $paidTraining;

        return $this;
    }

    public function getYouthGuarantee(): ?int
    {
        return $this->youthGuarantee;
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

    public function setMaintenance(?int $maintenance): self
    {
        $this->maintenance = $maintenance;

        return $this;
    }

    public function getActivityBonus(): ?int
    {
        return $this->activityBonus;
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

    public function setPensionBenefit(?int $pensionBenefit): self
    {
        $this->pensionBenefit = $pensionBenefit;

        return $this;
    }

    public function getMinimumIncome(): ?int
    {
        return $this->minimumIncome;
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

    public function setSalary(?int $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getRessourceOther(): ?int
    {
        return $this->ressourceOther;
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

    public function getRessourceOtherAmt(): ?float
    {
        return $this->ressourceOtherAmt;
    }

    public function setRessourceOtherAmt(?float $ressourceOtherAmt): self
    {
        $this->ressourceOtherAmt = $ressourceOtherAmt;

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

    public function getDebtsAmt(): ?float
    {
        return $this->debtsAmt;
    }

    public function setDebtsAmt(?float $debtsAmt): self
    {
        $this->debtsAmt = $debtsAmt;

        return $this;
    }

    public function getProfStatus(): ?int
    {
        return $this->profStatus;
    }

    public function setProfStatus(?int $profStatus): self
    {
        $this->profStatus = $profStatus;

        return $this;
    }

    public function getContractType(): ?int
    {
        return $this->contractType;
    }

    public function setContractType(?int $contractType): self
    {
        $this->contractType = $contractType;

        return $this;
    }

    public function getSupportPerson(): ?SupportPerson
    {
        return $this->supportPerson;
    }

    public function setSupportPerson(?SupportPerson $supportPerson): self
    {
        $this->supportPerson = $supportPerson;

        return $this;
    }
}
