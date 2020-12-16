<?php

namespace App\Form\Evaluation;

use App\Entity\Evaluation\EvalBudgetGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvalBudgetGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('cafId')
        ->add('contributionAmt', MoneyType::class, [
            'attr' => ['class' => 'js-money text-right'],
            'required' => false,
        ]);
        // ->add('commentEvalBudget', null, [
        //     'label_attr' => ['class' => 'sr-only'],
        //     'attr' => [
        //         'rows' => 5,
        //          'class => 'justify',
        //         'placeholder' => 'evalBudgetPerson.comment',
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
