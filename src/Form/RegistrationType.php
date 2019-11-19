<?php

namespace App\Form;

use App\Entity\User;
use App\Utils\Choices;

use App\Form\RoleUserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("firstname", null, [
                "attr" => [
                    "class" => "text-capitalize",
                    "placeholder" => "Firstname",
                    "autocomplete" => "off"
                ],
            ])
            ->add("lastname", null, [
                "attr" => [
                    "class" => "text-capitalize",
                    "placeholder" => "Lastname",
                    "autocomplete" => "off"
                ]
            ])
            ->add("username", null, [
                "attr" => [
                    "placeholder" => "Login",
                    "autocomplete" => "off"
                ],
                "help" => "Remplissage auto. en fonction du nom et prénom.",
            ])
            ->add("email", null, [
                "attr" => [
                    "placeholder" => "Email"
                ]
            ])
            ->add("roles", ChoiceType::class, [
                "label" => "Rôle",
                "data" => false,
                'placeholder' => "-- Select --",
                "choices" => Choices::getChoices(User::ROLES)
            ])
            ->add("password", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "Password",
                ],
                "help" => "6 caractères minimum dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial (? ! * { } [ ]- + = & < > $)",

            ])
            ->add("confirmPassword", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "Confirm password",
                ],
                "help" => "Veuillez re-saisir le mot de passe pour confirmation",
            ])
            ->add("roleUser", CollectionType::class, [
                "entry_type" => RoleUserType::class,
                "label" => false,
                "allow_add" => true,
                "allow_delete" => true,
                "delete_empty" => true,
                "prototype" => true,
                "by_reference" => true,
                "entry_options" => [
                    "attr" => ["class" => "form-inline"],
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => User::class,
            "translation_domain" => "forms",
        ]);
    }
}
