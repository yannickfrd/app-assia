<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Entity\InitEvalGroup;
use App\Entity\EvalHousingGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class InitEvalGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("housingStatus", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalHousingGroup::HOUSING_STATUS),
                "attr" => [
                    "class" => "js-initEval",
                    "data-id" => "housingStatus"
                ],
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("siaoRequest", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS_NC),
                "attr" => [
                    "class" => "js-initEval",
                    "data-id" => "siaoRequest"
                ],
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("socialHousingRequest", ChoiceType::class, [
                "choices" => Choices::getChoices(Choices::YES_NO_IN_PROGRESS_NC),
                "attr" => [
                    "class" => "js-initEval",
                    "data-id" => "socialHousingRequest"
                ],
                "placeholder" => "-- Select --",
                "required" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => InitEvalGroup::class,
            "translation_domain" => "initEval"
        ]);
    }
}
