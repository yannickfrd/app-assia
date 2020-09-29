<?php

namespace App\Form\Device;

use App\Entity\Pole;
use App\Entity\Service;
use App\Form\Model\DeviceSearch;
use App\Form\Utils\Choices;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => [
                    'class' => 'w-max-200',
                    'placeholder' => 'device.name',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'label_attr' => ['class' => 'sr-only'],
                'placeholder' => 'placeholder.service',
                'required' => false,
            ])
            ->add('pole', EntityType::class, [
                'class' => Pole::class,
                'choice_label' => 'name',
                'label_attr' => ['class' => 'sr-only'],
                'placeholder' => 'placeholder.pole',
                'required' => false,
            ])
            ->add('disabled', ChoiceType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'choices' => Choices::getChoices(Choices::DISABLE),
                'placeholder' => 'placeholder.disabled',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeviceSearch::class,
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
