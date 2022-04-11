<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalInitGroup;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalInitGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('housingStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalHousingGroup::HOUSING_STATUS),
                'placeholder' => 'placeholder.select',
                'required' => false,
                'help' => 'evalInitGroup.housingStatus.help',
            ])
            ->add('siaoRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS_NC),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('socialHousingRequest', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS_NC),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalInitGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
