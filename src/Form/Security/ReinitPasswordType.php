<?php

namespace App\Form\Security;

use App\Entity\UserResetPass;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ReinitPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("username", null, [
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "attr" => [
                    "placeholder" => "Login"
                ]
            ])
            ->add("email", null, [
                "label_attr" => [
                    "class" => "sr-only"
                ],
                "attr" => [
                    "placeholder" => "Email"
                ]
            ])
            ->add("password", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "New password",
                ],
                "help" => "6 caractères minimum dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial (? ! * { } [ ]- + = & < > $)",
            ])
            ->add("confirmPassword", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "Confirm new password",
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => UserResetPass::class,
            "translation_domain" => "forms",
        ]);
    }
}
