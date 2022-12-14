<?php

namespace App\Form\Support\Payment;

use App\Entity\Support\Payment;
use App\Form\Model\Support\PaymentSearch;
use App\Form\Model\Support\SupportPaymentSearch;
use App\Form\Support\Support\DeletedSearchType;
use App\Form\Type\DateSearchType;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupportPaymentSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'multiple' => true,
                'attr' => [
                    'placeholder' => 'placeholder.type',
                    'size' => 1,
                ],
                'choices' => Choices::getChoices(Payment::TYPES),
                'required' => false,
            ])
            ->add('dateType', ChoiceType::class, [
                'choices' => Choices::getChoices(PaymentSearch::DATE_TYPE),
                'placeholder' => 'placeholder.dateType',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => PaymentSearch::class,
            ])
            ->add('export')
            ->add('deleted', DeletedSearchType::class, [
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupportPaymentSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
