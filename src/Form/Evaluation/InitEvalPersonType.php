<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Form\Type\ResourcesType;
use App\Form\Utils\EvaluationChoices;
use App\Entity\Evaluation\EvalAdmPerson;
use Symfony\Component\Form\AbstractType;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\InitEvalPerson;
use App\Entity\Evaluation\EvalSocialPerson;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class InitEvalPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Person */
        $person = $options['attr']['person'];

        $builder
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
                'required' => false,
            ])
            ->add('rightSocialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'rightSocialSecurity',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('socialSecurity', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalSocialPerson::SOCIAL_SECURITY),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'socialSecurity',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('comment', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 4,
                    'class' => 'justify',
                    'placeholder' => 'initEvalPerson.comment',
                ],
            ]);

        if ($person->getAge() < 16) {
            return;
        }

        $builder
            ->add('familyBreakdown', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_PARTIAL),
                'attr' => [
                    'data-twin-field' => 'familyBreakdown',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('friendshipBreakdown', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_PARTIAL),
                'attr' => [
                    'data-twin-field' => 'friendshipBreakdown',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('profStatus', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::PROF_STATUS),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'profStatus',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('contractType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalProfPerson::CONTRACT_TYPE),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'contractType',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])

            ->add('resources', ResourcesType::class)

            ->add('debts', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'attr' => [
                    'data-important' => 'true',
                    'data-twin-field' => 'debts',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('debtsAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'debtsAmt',
                    'data-twin-field' => 'debtsAmt',
                    'placeholder' => 'Amount',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InitEvalPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
