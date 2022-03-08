<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalBudgetDebt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalBudgetDebtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', HiddenType::class)
            ->add('amount', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'debt',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('comment', TextType::class, [
                'attr' => ['placeholder' => 'debt.comment.placeholder'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalBudgetDebt::class,
        ]);
    }
}
