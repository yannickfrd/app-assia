<?php

namespace App\Form\Support\Evaluation;

use App\Entity\sitHousingGroup;

use App\Form\Utils\Choices;
use App\Form\Utils\SelectList;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SitHousingGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("housingStatus", ChoiceType::class, [
                "choices" => Choices::getChoices(sitHousingGroup::HOUSING_STATUS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("dls", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("dlsId")
            ->add("dlsDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("dlsRenewalDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("housingWishes")
            ->add("citiesWishes")
            ->add("specificities")
            ->add("syplo", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("syploId")
            ->add("syploDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("daloCommission", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("daloRecordDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("requalifiedDalo", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("decisionDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("hsgActionEligibility", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("hsgActionRecord", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("hsgActionDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("hsgActionDept", null, [
                "attr" => [
                    "class" => "js-dept-code",
                ]
            ])
            ->add("hsgActionRecordId")
            ->add("expulsionInProgress", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("publicForce", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("publicForceDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("expulsionComment")
            ->add("housingExperience", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("housingExpeComment")
            ->add("fsl")
            ->add("fslEligibility")
            ->add("cafEligibility")
            ->add("otherHelps")
            ->add("hepsPrecision")
            ->add("domiciliation", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("domiciliationAddress")
            ->add("domiciliationCity")
            ->add("domiciliationDept", null, [
                "attr" => [
                    "class" => "js-dept-code",
                ]
            ])
            ->add("commentSitHousing", null, [
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the housing situation"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => sitHousingGroup::class,
            "translation_domain" => "sitHousing"
        ]);
    }
}
