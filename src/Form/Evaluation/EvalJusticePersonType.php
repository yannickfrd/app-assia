<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Entity\EvalJusticePerson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EvalJusticePersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("justiceStatus", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalJusticePerson::JUSTICE_STATUS),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("justiceAct", ChoiceType::class, [
                "choices" => Choices::getChoices(EvalJusticePerson::JUSTICE_ACT),
                "placeholder" => "-- Select --",
                "required" => false
            ])
            ->add("commentEvalJustice", TextareaType::class, [
                "label_attr" => ["class" => "sr-only"],
                "attr" => [
                    "rows" => 4,
                    "placeholder" => "Write a comment about the justice situation of the person"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => EvalJusticePerson::class,
            "translation_domain" => "evaluation",
        ]);
    }
}
