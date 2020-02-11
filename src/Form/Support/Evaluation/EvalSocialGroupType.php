<?php

namespace App\Form\Support\Evaluation;

use App\Entity\EvalSocialGroup;

use App\Form\Utils\Choices;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalSocialGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("reasonRequest", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalSocialGroup::REASON_REQUEST),
                "placeholder" => "-- Select --",
                "required" => false

            ])
            ->add("wanderingTime", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalSocialGroup::WANDERING_TIME),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("speAnimal")
            ->add("speAnimalName")
            ->add("speWheelchair")
            ->add("speReducedMobility")
            ->add("speViolenceVictim")
            ->add("speDomViolenceVictim")
            ->add("speASE")
            ->add("speOther")
            ->add("speOtherPrecision")
            ->add("speComment")
            ->add("commentEvalSocial", null, [
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Write a comment about the social situation"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvalSocialGroup::class,
            "translation_domain" => "social"
        ]);
    }
}
