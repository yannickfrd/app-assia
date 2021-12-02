<?php

namespace App\Form\Admin\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['placeholder' => 'Login'],
            ])
            ->add('password', PasswordType::class, [
                'label_attr' => ['class' => 'sr-only'],
                'attr' => ['placeholder' => 'Password'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
            'translation_domain' => 'forms',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
