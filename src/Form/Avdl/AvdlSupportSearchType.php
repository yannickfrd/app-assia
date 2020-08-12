<?php

namespace App\Form\Avdl;

use App\Form\Utils\Choices;
use App\Form\Model\AvdlSupportSearch;
use App\Form\Type\DateSearchType;
use App\Form\Type\ServiceSearchType;
use App\Form\Model\SupportGroupSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AvdlSupportSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'placeholder' => 'Nom et/ou prénom',
                    'class' => 'w-max-170',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'multiple' => true,
                'choices' => Choices::getChoices(AvdlSupportSearch::STATUS),
                'attr' => [
                    'class' => 'multi-select js-status w-min-120',
                ],
                'placeholder' => '-- Status --',
                'required' => false,
            ])
            ->add('supportDates', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(SupportGroupSearch::SUPPORT_DATES),
                'placeholder' => '-- Date de suivi --',
                'required' => false,
            ])
            ->add('date', DateSearchType::class, [
                'data_class' => SupportGroupSearch::class,
                ])
                ->add('service', ServiceSearchType::class, [
                    'data_class' => AvdlSupportSearch::class,
                    'attr' => [
                        'options' => ['devices', 'referents'],
                        'serviceId' => 5,
                    ],
            ])
            ->add('diagOrSupport', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(AvdlSupportSearch::DIAG_OR_SUPPORT),
                'placeholder' => '-- Type de suivi --',
                'required' => false,
            ])
            ->add('readyToHousing', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(Choices::YES_NO),
                'placeholder' => '-- Prêt au logement --',
                'required' => false,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AvdlSupportSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
