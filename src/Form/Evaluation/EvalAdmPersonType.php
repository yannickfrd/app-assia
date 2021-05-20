<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use App\Entity\Evaluation\EvalAdmPerson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalAdmPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nationality', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalAdmPerson::NATIONALITY),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('arrivalDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('country')
            ->add('paper', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'paper',
                ],
                'placeholder' => 'placeholder.select',
                'help' => 'evalAdmPerson.paper.help',
                'required' => false,
            ])
            ->add('paperType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalAdmPerson::PAPER_TYPE),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'paperType',
                ],
                'placeholder' => 'placeholder.select',
                'help' => 'evalAdmPerson.paperType.help',
                'required' => false,
            ])
            ->add('asylumBackground', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('asylumStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalAdmPerson::RIGHT_TO_RESIDE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endValidPermitDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('renewalPermitDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('nbRenewals')
            ->add('workRight', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('commentEvalAdmPerson', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'evalAdmPerson.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalAdmPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
