<?php

namespace App\Form\Evaluation;

use App\Entity\EvalBudgetPerson;
use App\Form\Type\ResourcesType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalBudgetPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('resources', ResourcesType::class)
            ->add('incomeTax', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('incomeN1Amt', MoneyType::class, [
                'attr' => ['class' => 'js-money text-right'],
                'required' => false,
            ])
            ->add('incomeN2Amt', MoneyType::class, [
                'attr' => ['class' => 'js-money text-right'],
                'required' => false,
            ])
            ->add('resourcesComment')
            ->add('charges', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('chargesAmt', MoneyType::class, [
                'attr' => ['class' => 'js-chargesAmt text-right'],
                'required' => false,
            ]);

        foreach (EvalBudgetPerson::CHARGES_TYPE as $key => $value) {
            $builder
            ->add($key)
            ->add($key.'Amt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ]);
        }

        $builder
            ->add('chargeOtherPrecision', null, ['attr' => ['placeholder' => 'Other charge(s)...']])
            ->add('chargeComment')
            ->add('debts', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'attr' => [
                    'class' => 'js-initEval important',
                    'data-id' => 'debts',
                ],
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('debtsAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-debtsAmt js-initEval text-right',
                    'data-id' => 'debtsAmt',
                ],
                'required' => false,
            ]);

        foreach (EvalBudgetPerson::DEBTS_TYPE as $key => $value) {
            $builder->add($key);
        }

        $builder
            ->add('debtOtherPrecision', null, ['attr' => ['placeholder' => 'Other debt(s)...']])
            ->add('debtComment')
            ->add('monthlyRepaymentAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-repaymentAmt text-right'],
                'required' => false,
            ])
            ->add('overIndebtRecord', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
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
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('endRightsDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'help' => 'Date de fin des prestations ou des allocations',
            ])
            ->add('commentEvalBudget', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Write a comment about the budget situation',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalBudgetPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
