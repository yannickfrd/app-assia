<?php

namespace App\Form;

use App\Entity\UserResetPass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ReinitPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("username", null, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Login"
                ]
            ])
            ->add("email", null, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Email"
                ]
            ])
            ->add("password", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "New password",
                ]
            ])
            ->add("confirmPassword", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "Confirm new password",
                ]
            ]);
            // ->add("password", RepeatedType::class, [
            //     "type" => PasswordType::class,
            //     "invalid_message" => "Le mot de passe et la confirmation sont différents.",
            //     "options" => ["attr" => ["class" => "password-field"]],
            //     "required" => true,
            //     "first_options"  => [
            //         "label" => false,
            //         "attr" => [
            //             "placeholder" => "Nouveau mot de passe"
            //         ]
            //     ],
            //     "second_options" => [
            //         "label" => false,
            //         "attr" => [
            //             "placeholder" => "Confirmation nouveau mot de passe"
            //         ]
            //     ],
            // ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => UserResetPass::class,
            "translation_domain" => "forms",
        ]);
    }
}
