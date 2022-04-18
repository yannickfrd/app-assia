<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalFamilyGroup;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalFamilyGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('childrenBehind', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('famlReunification', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyGroup::FAML_REUNIFICATION),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('nbPeopleReunification')
            ->add('commentEvalFamilyGroup', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'evalFamilyGroup.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalFamilyGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
