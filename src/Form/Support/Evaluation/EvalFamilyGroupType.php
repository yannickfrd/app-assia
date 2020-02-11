<?php

namespace App\Form\Support\Evaluation;

use App\Entity\EvalFamilyGroup;

use App\Form\Utils\Choices;
use App\Form\Utils\SelectList;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalFamilyGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("nbDependentChildren")
            ->add("unbornChild", ChoiceType::class, [
                "choices" => Choices::getChoices(SelectList::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("expDateChildbirth", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("pregnancyType", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalFamilyGroup::PREGNANCY_TYPE),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("famlReunification", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalFamilyGroup::FAML_REUNIFICATION),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("nbPeopleReunification")
            ->add("cafId")
            ->add("commentEvalFamilyGroup", null, [
                "label_attr" => ["class" => "col-sm-12"],
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the family situation"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvalFamilyGroup::class,
            "translation_domain" => "family"
        ]);
    }
}
