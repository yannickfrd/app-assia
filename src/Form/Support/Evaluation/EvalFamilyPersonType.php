<?php

namespace App\Form\Support\Evaluation;

use App\Entity\EvalFamilyPerson;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalFamilyPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("maritalStatus", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalFamilyPerson::MARITAL_STATUS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("childcareSchool", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalFamilyPerson::CHILDCARE_SCHOOL),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("childcareSchoolLocation")
            ->add("childToHost", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalFamilyPerson::CHILD_TO_HOST),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("childDependance", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalFamilyPerson::CHILD_DEPENDANCE),
                "placeholder" => "-- Select --",
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvalFamilyPerson::class,
            "translation_domain" => "family",
        ]);
    }
}
