<?php

namespace App\Form\Accommodation;

use App\Entity\Device;
use App\Entity\Pole;
use App\Entity\Service;
use App\Form\Model\AccommodationSearch;
use App\Form\Utils\Choices;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140',
                    'placeholder' => 'Name',
                ],
            ])
            ->add('placesNumber', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140',
                    'placeholder' => 'Places number',
                ],
            ])
            ->add('supportDates', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(AccommodationSearch::ACCOMMODATION_DATES),
                'placeholder' => '-- Date --',
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-max-165',
                ],
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-max-165',
                ],
                'required' => false,
            ])
            ->add('city', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'class' => 'w-max-140',
                    'placeholder' => 'City',
                ],
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'multiple' => true,
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'placeholder' => '-- Service --',
                'attr' => [
                    'class' => 'multi-select js-service',
                ],
                'required' => false,
            ])
            ->add('device', EntityType::class, [
                'class' => Device::class,
                'choice_label' => 'name',
                'multiple' => true,
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'placeholder' => '-- Device --',
                'attr' => [
                    'class' => 'multi-select js-device',
                ],
                'required' => false,
            ])
            ->add('pole', EntityType::class, [
                'class' => Pole::class,
                'choice_label' => 'name',
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'placeholder' => '-- Pole --',
                'required' => false,
            ])
            ->add('export');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AccommodationSearch::class,
            'method' => 'get',
            'translation_domain' => 'forms',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
