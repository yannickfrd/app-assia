<?php

namespace App\Form\Evaluation;

use App\Entity\EvalProfPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalProfPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("schoolLevel", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalProfPerson::SCHOOL_LEVEL),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("profExperience", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalProfPerson::PROF_EXPERIENCE),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("profStatus", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalProfPerson::PROF_STATUS),
                "attr" => [
                    "class" => "js-initEval",
                    "data-id" => "profStatus"
                ],
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("jobCenterId")
            ->add("contractType", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalProfPerson::CONTRACT_TYPE),
                "attr" => [
                    "class" => "js-initEval",
                    "data-id" => "contractType"
                ],
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("jobType")
            ->add("contractStartDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("contractEndDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("nbWorkingHours")
            ->add("workingHours")
            ->add("workPlace")
            ->add("employerName")
            ->add("transportMeans")
            ->add("rqth", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("endRqthDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("commentEvalProf", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the professional situation"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvalProfPerson::class,
            "translation_domain" => "prof",
        ]);
    }
}
