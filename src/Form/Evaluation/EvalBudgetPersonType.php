<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Form\Type\ResourcesType;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use App\Entity\Evaluation\EvalBudgetPerson;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalBudgetPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('resources', ResourcesType::class)
            ->add('incomeTax', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('incomeN1Amt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'true',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('incomeN2Amt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'true',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('resourcesComment')
            ->add('charges', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('chargesAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'chargesAmt',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ]);

        foreach (EvalBudgetPerson::CHARGES_TYPE as $key => $value) {
            $builder
            ->add($key)
            ->add($key.'Amt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'charges',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ]);
        }

        $builder
            ->add('chargeOtherPrecision', null, ['attr' => ['placeholder' => 'Other charge(s)...']])
            ->add('chargeComment')
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

        foreach (EvalBudgetPerson::DEBTS_TYPE as $key => $value) {
            $builder->add($key);
        }

        $builder
            ->add('debtOtherPrecision', null, ['attr' => ['placeholder' => 'Other debt(s)...']])
            ->add('debtComment')
            ->add('monthlyRepaymentAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'repaymentAmt',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('overIndebtRecord', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('overIndebtRecordDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('settlementPlan', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalBudgetPerson::SETTLEMENT_PLAN),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('moratorium', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endRightsDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'help' => 'evalBudgetPerson.endRightsDate.help',
            ])
            ->add('commentEvalBudget', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'class' => 'justify',
                    'placeholder' => 'evalBudgetPerson.comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalBudgetPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
