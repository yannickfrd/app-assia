<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use App\Entity\Evaluation\InitEvalGroup;
use Symfony\Component\Form\AbstractType;
use App\Entity\Evaluation\EvalHousingGroup;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class InitEvalGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('housingStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalHousingGroup::HOUSING_STATUS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
                'help' => 'initEvalGroup.housingStatus.help',
            ])
            ->add('siaoRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS_NC),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('socialHousingRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS_NC),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'true',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InitEvalGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
