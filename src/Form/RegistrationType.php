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
            ->add("firstname", NULL, [
                "label" => "Prénom",
                "attr" => [
                    "placeholder" => "Prénom"
                ]
            ])
            ->add("lastname", NULL, [
                "label" => "Nom",
                "attr" => [
                    "placeholder" => "Nom"
                ]
            ])
            ->add("username", NULL, [
                "label" => "Nom d'utilisateur",
                "attr" => [
                    "placeholder" => "Nom d'utilisateur"
                ]
            ])
            ->add("email", NULL, [
                "label" => "Adresse email",
                "attr" => [
                    "placeholder" => "Adresse email"
                ]
            ])
            ->add("password", PasswordType::class, [
                "label" => "Mot de passe",
                "attr" => [
                    "placeholder" => "Mot de passe"
                ]
            ])
            ->add("confirmPassword", PasswordType::class, [
                "label" => "Confirmation du mot de passe",
                "attr" => [
                    "placeholder" => "Confirmation du mot de passe"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
