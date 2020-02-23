<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Entity\EvalAdmPerson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalAdmPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("nationality", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalAdmPerson::NATIONALITY),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("country")
            ->add("paper", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("paperType", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalAdmPerson::PAPER_TYPE),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("asylumBackground", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("asylumStatus", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalAdmPerson::RIGHT_TO_RESIDE),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("endValidPermitDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("renewalPermitDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("nbRenewals")
            ->add("workRight", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("commentEvalAdmPerson", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the administrative situation"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvalAdmPerson::class,
            "translation_domain" => "adm",
        ]);
    }
}
