<?php

namespace App\Form\Evaluation;

use App\Entity\EvalSocialGroup;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalSocialGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('animalType')
            ->add('commentEvalSocialGroup', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'evalSocialGroup.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalSocialGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
