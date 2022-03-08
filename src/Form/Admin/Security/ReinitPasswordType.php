<?php

namespace App\Form\Admin\Security;

use App\Entity\Organization\User;
use Symfony\Component\Form\AbstractType;
use App\Form\Model\Security\UserResetPassword;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ReinitPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ->add('confirmPassword', PasswordType::class, [
                'attr' => [
                    'class' => 'js-password',
                    'placeholder' => 'Confirm new password',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserResetPassword::class,
            'translation_domain' => 'forms',
        ]);
    }
}
