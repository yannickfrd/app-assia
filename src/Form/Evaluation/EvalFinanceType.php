<?php

namespace App\Form\Evaluation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EvalFinanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', HiddenType::class, [
                    'required' => true,
            ])
            ->add('amount', MoneyType::class, [
                'attr' => [
                    'placeholder' => 'Amount',
                ],
                'required' => false,
             ])
            ->add('comment', TextType::class, [
                'attr' => ['placeholder' => 'Other'],
            ])
        ;
    }
}
