<?php

namespace App\Form\Admin\Security;

use App\Entity\Organization\User;
use App\Form\Organization\User\UserDeviceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecurityUserDevicesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userDevices', CollectionType::class, [
                'entry_type' => UserDeviceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'prototype' => true,
                'by_reference' => false,
                'label_attr' => ['class' => 'sr-only'],
                'entry_options' => [
                    'attr' => ['class' => 'form-inline'],
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'userDevice';
    }
}
