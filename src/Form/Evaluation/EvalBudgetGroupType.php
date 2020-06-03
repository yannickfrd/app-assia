<?php

namespace App\Form\Evaluation;

use App\Entity\EvalBudgetGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalBudgetGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('contributionAmt', MoneyType::class, [
            'attr' => ['class' => 'text-right'],
            'required' => false,
        ]);
        // ->add('incomeN1Amt', MoneyType::class, [
            //     'attr' => ['class' => 'text-right'],
            //     'required' => false,
            // ])
            // ->add('incomeN2Amt', MoneyType::class, [
            //     'attr' => ['class' => 'text-right'],
            //     'required' => false,
            // ])
            // ->add('commentEvalBudget', null, [
            //     'label_attr' => ['class' => 'sr-only'],
            //     'attr' => [
            //         'rows' => 5,
            //         'placeholder' => 'Write a comment about the budget situation',
            //     ],
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EvalBudgetGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
