<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalSocialGroup;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalSocialGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reasonRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialGroup::REASON_REQUEST),
                'attr' => ['autocomplete' => 'true'],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('wanderingTime', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialGroup::WANDERING_TIME),
                'attr' => ['autocomplete' => 'true'],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('animal', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('animalType')
            ->add('commentEvalSocialGroup', null, [
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'evalSocialGroup.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalSocialGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
