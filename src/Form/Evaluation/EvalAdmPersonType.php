<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalAdmPerson;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalAdmPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'asylumBackground',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('asylumStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalAdmPerson::ASYLUM_STATUS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'asylumStatus',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('AgdrefId', null, [
                'attr' => ['data-mask-type' => 'number'],
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalAdmPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
