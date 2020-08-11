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
            ->add('periodContribution', DateType::class, [
                'required' => true,
                'years' => range((int) date('Y'), (int) date('Y') - 10),
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'contribution.type',
                'choices' => Choices::getchoices(Contribution::CONTRIBUTION_TYPE),
                'placeholder' => 'placeholder.select',
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
            ->add('housingAssitanceAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
                'required' => false,
            ])
            ->add('rentAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
                ],
                'required' => false,
            ])
            ->add('toPayAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'js-money text-right',
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
                ],
                'required' => false,
            ])
            ->add('stillToPayAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'readonly' => true,
                ],
                'required' => false,
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
