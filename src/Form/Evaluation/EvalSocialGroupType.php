<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use App\Entity\Evaluation\EvalSocialGroup;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalSocialGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reasonRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialGroup::REASON_REQUEST),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('wanderingTime', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialGroup::WANDERING_TIME),
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
                'label_attr' => ['class' => 'sr-only'],
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
