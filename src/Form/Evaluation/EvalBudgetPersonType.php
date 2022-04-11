<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalBudgetCharge;
use App\Entity\Evaluation\EvalBudgetDebt;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\Resource;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalBudgetPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('resource', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalBudgetPerson::RESOURCES),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('resourceType', ChoiceType::class, [
                'choices' => Choices::getChoices(Resource::RESOURCES),
                'attr' => ['data-twin-field' => 'true'],
                'placeholder' => 'placeholder.add',
                'mapped' => false,
                'required' => false,
            ])
            ->add('resourcesAmt', MoneyType::class)
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
            ->add('charge', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('chargeType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalBudgetCharge::CHARGES),
                'placeholder' => 'placeholder.add',
                'mapped' => false,
                'required' => false,
            ])
            ->add('chargesAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'chargesAmt',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('chargeOtherPrecision', null, ['attr' => ['placeholder' => 'Other charge(s)...']])
            ->add('chargeComment')
            ->add('debt', ChoiceType::class, [
                'choices' => Choices::getChoices(EvaluationChoices::YES_NO),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('debtType', ChoiceType::class, [
                'choices' => Choices::getChoices(EvalBudgetDebt::DEBTS),
                'placeholder' => 'placeholder.add',
                'mapped' => false,
                'required' => false,
            ])
            ->add('debtsAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'debtsAmt',
                    'data-twin-field' => 'true',
                    'placeholder' => 'Amount',
                ],
            ])
            ->add('debtOtherPrecision', null, ['attr' => ['placeholder' => 'Other debt(s)...']])
            ->add('debtComment')
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
            ])
        ;

        $finances = [
            'evalBudgetResources' => EvalBudgetResourceType::class,
            'evalBudgetCharges' => EvalBudgetChargeType::class,
            'evalBudgetDebts' => EvalBudgetDebtType::class,
        ];

        foreach ($finances as $childName => $className) {
            $builder->add($childName, CollectionType::class, [
                'entry_type' => $className,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalBudgetPerson::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
