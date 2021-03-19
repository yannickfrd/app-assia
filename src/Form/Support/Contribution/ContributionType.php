<?php

namespace App\Form\Support\Contribution;

use App\Entity\Support\Contribution;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'label' => 'contribution.startDate',
                'widget' => 'single_text',
                ])
                ->add('endDate', DateType::class, [
                'label' => 'contribution.endDate',
                'widget' => 'single_text',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'contribution.type',
                'choices' => Choices::getchoices(Contribution::CONTRIBUTION_TYPE),
                'placeholder' => 'placeholder.select',
            ])
            ->add('resourcesAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('aplAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('rentAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('toPayAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('paymentDate', DateType::class, [
                'label' => 'Date de l\'opération',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('paymentType', ChoiceType::class, [
                'choices' => Choices::getchoices(Contribution::PAYMENT_TYPE),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('paidAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('stillToPayAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'placeholder' => 'Amount',
                    'readonly' => true,
                ],
                'required' => false,
            ])
            ->add('returnAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'placeholder.comment',
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
