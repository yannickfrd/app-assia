<?php

namespace App\Form\Evaluation;

use App\Form\Utils\Choices;
use App\Entity\EvalBudgetPerson;
use App\Form\Type\ResourcesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvalBudgetPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('resources', ResourcesType::class)

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
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('chargesAmt', MoneyType::class, [
                'attr' => ['class' => 'js-chargesAmt text-right'],
                'required' => false,
            ])
            ->add('rent')
            ->add('electricityGas')
            ->add('water')
            ->add('insurance')
            ->add('mutual')
            ->add('taxes')
            ->add('transport')
            ->add('childcare')
            ->add('alimony')
            ->add('phone')
            ->add('chargeOther', null, ['label_attr' => ['class' => 'js-noText']])
            ->add('chargeOtherPrecision', null, ['attr' => ['placeholder' => 'Other charge(s)...']])
            ->add('rentAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('electricityGasAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('waterAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('insuranceAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('mutualAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('taxesAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('transportAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('childcareAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('alimonyAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('phoneAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('chargeOtherAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-charges text-right'],
                'required' => false,
            ])
            ->add('chargeComment')

            ->add('debts', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO),
                'attr' => [
                    'class' => 'js-initEval',
                    'data-id' => 'debts',
                ],
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('debtsAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money js-debtsAmt js-initEval text-right',
                    'data-id' => 'debtsAmt',
                ],
                'required' => false,
            ])
            ->add('debtRental')
            ->add('debtConsrCredit')
            ->add('debtMortgage')
            ->add('debtFines')
            ->add('debtTaxDelays')
            ->add('debtBankOverdrafts')
            ->add('debtOther')
            ->add('debtOtherPrecision', null, ['attr' => ['placeholder' => 'Other debt(s)...']])
            ->add('debtComment')
            ->add('monthlyRepaymentAmt', MoneyType::class, [
                'attr' => ['class' => 'js-money js-repaymentAmt text-right'],
                'required' => false,
            ])
            ->add('overIndebtRecord', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('overIndebtRecordDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('settlementPlan', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalBudgetPerson::SETTLEMENT_PLAN),
                'placeholder' => '-- Select --',
                'required' => false,
            ])
            ->add('moratorium', ChoiceType::class, [
                'choices' => Choices::getChoices(Choices::YES_NO_IN_PROGRESS),
                'placeholder' => '-- Select --',
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
