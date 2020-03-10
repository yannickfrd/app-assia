<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Entity\EvalSocialPerson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalSocialPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("rightSocialSecurity", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                "attr" => [
                    "class" => "js-initEval",
                    "data-id" => "rightSocialSecurity"
                ],
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("socialSecurity", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalSocialPerson::SOCIAL_SECURITY),
                "attr" => [
                    "class" => "js-initEval",
                    "data-id" => "socialSecurity"
                ],
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("socialSecurityOffice")
            ->add("endRightsSocialSecurityDate", DateType::class, [
                "widget" => "single_text",
                "required" => false
            ])
            ->add("childWelfareBackground", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false,
                "help" => "Aide sociale à l'enfance, PJJ…"
            ])
            ->add("familyBreakdown", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_PARTIAL),
                "attr" => [
                    "class" => "js-initEval",
                    "data-id" => "familyBreakdown"
                ],
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("friendshipBreakdown", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_PARTIAL),
                "attr" => [
                    "class" => "js-initEval",
                    "data-id" => "friendshipBreakdown"
                ],
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("healthProblem", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("physicalHealthProblem")
            ->add("mentalHealthProblem")
            ->add("addictionProblem")
            ->add("careSupport", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("careSupportType", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalSocialPerson::CARE_SUPPORT),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("wheelchair")
            ->add("reducedMobility")
            ->add("violenceVictim", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("domViolenceVictim", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("commentEvalSocialPerson", null, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "rows" => 4,
                    "placeholder" => "Write a comment about the social situation of the person"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvalSocialPerson::class,
            "translation_domain" => "evaluation",
        ]);
    }
}
