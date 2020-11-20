<?php

namespace App\Form\Security;

use App\Form\Model\UserChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'js-password',
                    'placeholder' => 'Password',
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'js-password',
                    'placeholder' => 'New password',
                ],
                'help' => 'user.password.help',
            ])
            ->add('confirmNewPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'js-password',
                    'placeholder' => 'Confirm new password',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserChangePassword::class,
            'translation_domain' => 'forms',
        ]);
    }
}
