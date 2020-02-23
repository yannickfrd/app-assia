<?php

namespace App\Form;

use App\Form\Utils\Choices;
use App\Entity\EvalAdmPerson;
use App\Entity\EvalProfPerson;
use App\Entity\InitEvalPerson;
use App\Entity\EvalSocialPerson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class InitEvalPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("paperType", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalAdmPerson::PAPER_TYPE),
                "attr" => ["class" => "border-warning"],
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("rightSocialSecurity", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("socialSecurity", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalSocialPerson::SOCIAL_SECURITY),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("familyBreakdown", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_PARTIAL),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("friendshipBreakdown", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_PARTIAL),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("profStatus", ChoiceType::class, [
                "attr" => ["class" => "border-warning"],
                "choices" => Choices::getChoices(EvalProfPerson::PROF_STATUS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("contractType", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalProfPerson::CONTRACT_TYPE),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("resources", ChoiceType::class, [
                "attr" => ["class" => "border-warning"],
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                "placeholder" => "-- Select --",
                "required" => false
            ])


            ->add("resourcesAmt", MoneyType::class, [
                "attr" => ["class" => "js-resourcesAmt text-right"],
                "required" => false
            ])
            ->add("disAdultAllowance")
            ->add("disChildAllowance")
            ->add("unemplBenefit")
            ->add("asylumAllowance")
            ->add("tempWaitingAllowance")
            ->add("familyAllowance")
            ->add("solidarityAllowance")
            ->add("paidTraining")
            ->add("youthGuarantee")
            ->add("maintenance")
            ->add("activityBonus")
            ->add("pensionBenefit")
            ->add("minimumIncome")
            ->add("salary")
            ->add("ressourceOther", null, ["label_attr" => ["class" => "js-noText"]])
            ->add("ressourceOtherPrecision", null, ["attr" => ["placeholder" => "Autre ressource..."]])
            ->add("disAdultAllowanceAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("disChildAllowanceAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("unemplBenefitAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("asylumAllowanceAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("tempWaitingAllowanceAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("familyAllowanceAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("solidarityAllowanceAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("paidTrainingAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("youthGuaranteeAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("maintenanceAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("activityBonusAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("pensionBenefitAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("minimumIncomeAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("salaryAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])
            ->add("ressourceOtherAmt", MoneyType::class, [
                "attr" => ["class" => "js-resources text-right"],
                "required" => false
            ])


            ->add("debts", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("debtsAmt", MoneyType::class, [
                "attr" => ["class" => "js-debtsAmt text-right"],
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => InitEvalPerson::class,
            "translation_domain" => "initEval"
        ]);
    }
}
