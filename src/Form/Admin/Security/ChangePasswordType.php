<?php

namespace App\Form\Admin\Security;

use App\Entity\Organization\User;
use App\Form\Model\Security\UserChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'js-password',
                    'placeholder' => 'Password',
                ],
            ])
            ->add('newPassword', PasswordType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => User::PASSWORD_REGEX_PATTERN,
                        'match' => true,
                        'message' => 'Le mot de passe est invalide.',
                    ]),
                ],
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserChangePassword::class,
            'translation_domain' => 'forms',
        ]);
    }
}
