<?php

namespace App\Form;

use App\Entity\User;
use App\Utils\Choices;
use App\Entity\RoleUser;
use App\Form\RoleUserType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                    "class" => "text-uppercase",
                    "placeholder" => "Lastname",
                    "autocomplete" => "off"
                ]
            ])
            ->add("username", null, [
                "attr" => [
                    "placeholder" => "Login",
                    "autocomplete" => "off"
                ],
                "help" => "Remplissage automatique en fonction de votre nom et prénom.",
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
                ],
                "help" => "6 caractères minimum dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial ( ? ! * ( ) { } [ ]- + = & < > $).",

            ])
            ->add("confirmPassword", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "Confirm password",
                ],
                "help" => "Veuillez re-saisir le mot de passe pour confirmation",
            ])
            ->add('roleUser', CollectionType::class, [
                // each entry in the array will be an "email" field
                'entry_type' => RoleUser::class,
                // these options are passed to each "email" type
                'entry_options' => [
                    'attr' => ['class' => 'email-box'],
                ]
            ]);
        // ->add("roleUser", RoleUserType::class, [
        //     "data_class" => RoleUser::class,
        //     "label" => false,
        // ]);
        // ->add("roleUser", ChoiceType::class, [
        //     'placeholder' => "-- Rôle --",
        //     "label" => false,
        //     "required" => false,
        //     "choices" => Choices::getChoices(RoleUser::ROLE),
        //     "attr" => [
        //         "class" => "",
        //     ]
        // ]);
        // ->add("roleUser", EntityType::class, [
        //     "class" => RoleUser::class,
        //     "choice_label" => "role",
        //     "placeholder" => "-- Select --",
        //     "required" => false,
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => User::class,
            "translation_domain" => "forms",
        ]);
    }
}
