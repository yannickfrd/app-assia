<?php

namespace App\Form\Support\Payment;

use App\Entity\Organization\Service;
use App\Entity\Support\Payment;
use App\Form\Utils\Choices;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Payment */
        $payment = $builder->getData();
        $service = $payment->getSupportGroup() ? $payment->getSupportGroup()->getService() : null;

        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'payment.type',
                'choices' => Choices::getChoices($this->getOptionTypes($service)),
                'placeholder' => 'placeholder.select',
            ])
            ->add('startDate', DateType::class, [
                'label' => 'payment.startDate',
                'widget' => 'single_text',
                ])
            ->add('endDate', DateType::class, [
                'label' => 'payment.endDate',
                'widget' => 'single_text',
            ])
            ->add('repaymentReason', ChoiceType::class, [
                'choices' => Choices::getChoices(Payment::REPAYMENT_REASONS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('resourcesAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'true',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('chargesAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'true',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('contributionRate', null, [
                'label' => 'payment.contributionType',
                'attr' => ['readonly' => true],
            ])
            ->add('nbConsumUnits', null, [
                'attr' => ['readonly' => true],
            ])
            ->add('toPayAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'true',
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
                'choices' => Choices::getChoices(Payment::PAYMENT_TYPES),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ])
            ->add('paidAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'true',
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
                    'class' => 'text-right',
                    'data-amount' => 'true',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('comment', null, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'payment.comment.placeholder',
                ],
                'help' => 'payment.comment.help',
            ])
            ->add('commentExport', null, [
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'payment.commentExport.placeholder',
                ],
                'help' => 'payment.commentExport.help',
            ]);

        // if (Service::SERVICE_TYPE_HEB === $serviceType) {
        $builder
            ->add('aplAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'true',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ])
            ->add('rentAmt', MoneyType::class, [
                'attr' => [
                    'class' => 'text-right',
                    'data-amount' => 'true',
                    'placeholder' => 'Amount',
                ],
                'required' => false,
            ]);
        // }

        // if (Service::SERVICE_TYPE_HOTEL === $serviceType) {
        $builder
            ->add('noContrib', CheckboxType::class, [
                'required' => false,
            ])
            ->add('noContribReason', ChoiceType::class, [
                'choices' => Choices::getChoices(Payment::NO_CONTRIB_REASONS),
                'placeholder' => 'placeholder.select',
                'required' => false,
            ]);
        // }
    }

    /**
     * @return array|ArrayCollection
     */
    public function getOptionTypes(?Service $service)
    {
        if (null === $service) {
            return Payment::TYPES;
        }

        if (Service::SERVICE_TYPE_HOTEL === $service->getType()) {
            return Payment::CONTRIBUTION_HOTEL_TYPES;
        }

        $types = new ArrayCollection(Payment::TYPES);

        if (Service::RENT_CONTRIBUTION !== $service->getContributionType()) {
            $types->remove(Payment::RENT);
        }

        return $types;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'allow_extra_fields' => true,
            'translation_domain' => 'forms',
        ]);
    }
}
