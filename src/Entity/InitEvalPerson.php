<?php

namespace App\Entity;

use App\Form\Utils\Choices;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
     * @Groups("export")
     */
    private $paperTypeToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $rightSocialSecurity;

    /**
     * @Groups("export")
     */
    private $rightSocialSecurityToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $socialSecurity;

    /**
     * @Groups("export")
     */
    private $socialSecurityToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $familyBreakdown;

    /**
     * @Groups("export")
     */
    private $familyBreakdownToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $friendshipBreakdown;

    /**
     * @Groups("export")
     */
    private $friendshipBreakdownToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $resources;

    /**
     * @Groups("export")
     */
    private $resourcesToString;

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
     * @Groups("export")
     */
    private $disAdultAllowanceToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $disChildAllowance;

    /**
     * @Groups("export")
     */
    private $disChildAllowanceToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $unemplBenefit;

    /**
     * @Groups("export")
     */
    private $unemplBenefitToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $asylumAllowance;

    /**
     * @Groups("export")
     */
    private $asylumAllowanceToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $tempWaitingAllowance;

    /**
     * @Groups("export")
     */
    private $tempWaitingAllowanceToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $familyAllowance;

    /**
     * @Groups("export")
     */
    private $familyAllowanceToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $solidarityAllowance;

    /**
     * @Groups("export")
     */
    private $solidarityAllowanceToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $paidTraining;

    /**
     * @Groups("export")
     */
    private $paidTrainingToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $youthGuarantee;

    /**
     * @Groups("export")
     */
    private $youthGuaranteeToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $maintenance;

    /**
     * @Groups("export")
     */
    private $maintenanceToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $activityBonus;

    /**
     * @Groups("export")
     */
    private $activityBonusToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $pensionBenefit;

    /**
     * @Groups("export")
     */
    private $pensionBenefitToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $minimumIncome;

    /**
     * @Groups("export")
     */
    private $minimumIncomeToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $salary;

    /**
     * @Groups("export")
     */
    private $salaryToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $ressourceOther;

    /**
     * @Groups("export")
     */
    private $ressourceOtherToString;

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
    private $ressourceOtherAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debts;

    /**
     * @Groups("export")
     */
    private $debtsToString;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $debtsAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $profStatus;

    /**
     * @Groups("export")
     */
    private $profStatusToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $contractType;

    /**
     * @Groups("export")
     */
    private $contractTypeToString;

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

    public function getPaperTypeToString(): ?string
    {
        return $this->paperType ? EvalAdmPerson::PAPER_TYPE[$this->paperType] : null;
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

    public function getRightSocialSecurityToString(): ?string
    {
        return $this->rightSocialSecurity ? Choices::YES_NO_IN_PROGRESS[$this->rightSocialSecurity] : null;
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

    public function getSocialSecurityToString(): ?string
    {
        return $this->socialSecurity ? EvalSocialPerson::SOCIAL_SECURITY[$this->socialSecurity] : null;
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

    public function getFamilyBreakdownToString(): ?string
    {
        return $this->familyBreakdown ? Choices::YES_NO_IN_PROGRESS[$this->familyBreakdown] : null;
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

    public function getFriendshipBreakdownToString(): ?string
    {
        return $this->friendshipBreakdown ? Choices::YES_NO_IN_PROGRESS[$this->friendshipBreakdown] : null;
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

    public function getResourcesToString(): ?string
    {
        return $this->resources ? Choices::YES_NO_IN_PROGRESS[$this->resources] : null;
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

    public function getDebtsToString(): ?string
    {
        return $this->debts ? Choices::YES_NO[$this->debts] : null;
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

    public function getProfStatusToString(): ?string
    {
        return $this->profStatus ? EvalProfPerson::PROF_STATUS[$this->profStatus] : null;
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

    public function getContractTypeToString(): ?string
    {
        return $this->contractType ? EvalProfPerson::CONTRACT_TYPE[$this->contractType] : null;
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
