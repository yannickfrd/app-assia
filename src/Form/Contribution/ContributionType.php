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
                // 'widget' => 'single_text',
                'required' => false,
            ])
            ->add('salaryAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
                'required' => false,
            ])
            ->add('otherAmt', MoneyType::class, [
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
                // 'required' => false,
            ])
            ->add('paymentType', ChoiceType::class, [
                'choices' => Choices::getchoices(Contribution::PAYMENT_TYPE),
                'placeholder' => '-- Select --',
            ])
            ->add('paymentAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
                'required' => false,
            ])
            ->add('stillDue', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
                'required' => false,
            ])
            ->add('returnDate', DateType::class, [
                'widget' => 'single_text',
                // 'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => Choices::getchoices(Contribution::CONTRIBUTION_TYPE),
                'placeholder' => '-- Select --',
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
            'translation_domain' => 'forms',
        ]);
    }
}
