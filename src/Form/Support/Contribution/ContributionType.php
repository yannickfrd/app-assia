<?php

namespace App\Form\Support\Contribution;

use App\Entity\Organization\Service;
use App\Entity\Support\Contribution;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Contribution */
        $contribution = $builder->getData();
        $serviceType = $contribution->getSupportGroup() ? $contribution->getSupportGroup()->getService()->getType() : null;

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
                'choices' => Choices::getchoices(Service::SERVICE_TYPE_HOTEL === $serviceType ?
                    Contribution::CONTRIBUTION_HOTEL_TYPE : Contribution::CONTRIBUTION_TYPE),
                'placeholder' => 'placeholder.select',
            ])
            ->add('resourcesAmt', MoneyType::class, [
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
                    'placeholder' => 'contribution.comment.placeholder',
                ],
                'help' => 'contribution.comment.help',
            ])
            ->add('commentExport', null, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'contribution.commentExport.placeholder',
                ],
                'help' => 'contribution.commentExport.help',
            ]);

        // if (Service::SERVICE_TYPE_HEB === $serviceType) {
        $builder
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
            ]);
        // }

        // if (Service::SERVICE_TYPE_HOTEL === $serviceType) {
        $builder
            ->add('noContrib', CheckboxType::class, [
                'label_attr' => ['class' => 'custom-control-label'],
                'attr' => ['class' => 'custom-control-input checkbox'],
                'required' => false,
            ])
            ->add('noContribReason', ChoiceType::class, [
                'choices' => Choices::getchoices(Contribution::NO_CONTRIB_REASON),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ]);
        // }
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
