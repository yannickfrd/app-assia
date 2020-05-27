<?php

namespace App\Form\Contribution;

use App\Form\Utils\Choices;
use App\Entity\Contribution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contribDate', DateType::class, [
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => Choices::getchoices(Contribution::CONTRIBUTION_TYPE),
                'placeholder' => '-- Select --',
            ])
            ->add('salaryAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
                'required' => false,
            ])
            ->add('resourcesAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
                'required' => false,
            ])
            ->add('credential')
            ->add('contribAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
                'required' => false,
            ])
            ->add('paymentDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('paymentType', ChoiceType::class, [
                'choices' => Choices::getchoices(Contribution::PAYMENT_TYPE),
                'placeholder' => '-- Select --',
            ])
            ->add('paymentAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
            ])
            ->add('stillDueAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'readonly' => true,
                ],
                'required' => false,
            ])
            ->add('returnDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('returnAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
                'required' => false,
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'Write a comment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contribution::class,
            'allow_extra_fields' => true,
            'translation_domain' => 'forms',
        ]);
    }
}