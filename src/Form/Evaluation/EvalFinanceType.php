<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\Resource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                    'class' => 'text-right',
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
