<?php

namespace App\Form\Security;

use App\Form\Model\UserResetPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReinitPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'placeholder' => 'Login',
                ],
            ])
            ->add('email', null, [
                'label_attr' => [
                    'class' => 'sr-only',
                ],
                'attr' => [
                    'placeholder' => 'Email',
                ],
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'class' => 'js-password',
                    'placeholder' => 'New password',
                ],
                'help' => 'user.password.help',
            ])
            ->add('confirmPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'js-password',
                    'placeholder' => 'Confirm new password',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserResetPassword::class,
            'translation_domain' => 'forms',
        ]);
    }
}
