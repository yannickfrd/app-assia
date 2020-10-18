<?php

namespace App\Form\Evaluation;

use App\Entity\EvalFamilyPerson;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalFamilyPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('maritalStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::MARITAL_STATUS),
                'attr' => ['class' => 'js-initEval important'],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('noConciliationOrder', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('unbornChild', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('expDateChildbirth', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('pregnancyType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::PREGNANCY_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('protectiveMeasure', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('protectiveMeasureType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::PROTECTIVE_MEASURE_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('childcareSchool', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::CHILDCARE_SCHOOL),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('childcareSchoolLocation')
            ->add('childToHost', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::CHILD_TO_HOST),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('childDependance', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalFamilyPerson::CHILD_DEPENDANCE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('commentEvalFamilyPerson', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'evalFamilyPerson.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalFamilyPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
