<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("firstname", null, [
                "attr" => [
                    "placeholder" => "Firstname"
                ]
            ])
            ->add("lastname", null, [
                "attr" => [
                    "class" => "text-uppercase",
                    "placeholder" => "Lastname"
                ]
            ])
            ->add("username", null, [
                "attr" => [
                    "placeholder" => "Username"
                ]
            ])
            ->add("email", null, [
                "attr" => [
                    "placeholder" => "Email"
                ]
            ])
            ->add("password", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "Password",
                ]
            ])
            ->add("confirmPassword", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "ConfirmPassword",
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            "translation_domain" => "forms",
        ]);
    }
}
