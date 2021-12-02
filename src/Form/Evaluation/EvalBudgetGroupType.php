<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalBudgetGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalBudgetGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('cafId')
        ->add('cafAttachment')
        ->add('contributionAmt', MoneyType::class, [
            'attr' => [
                'class' => 'text-right',
                'data-amount' => 'true',
            ],
            'required' => false,
        ])
        ->add('commentEvalBudget', null, [
            'label' => 'Comment',
            'attr' => [
                'rows' => 2,
                 'class' => 'justify',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvalBudgetGroup::class,
            'translation_domain' => 'evaluation',
        ]);
    }
}
