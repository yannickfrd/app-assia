<?php

namespace App\Form\Security;

use App\Entity\UserResetPassword;

use App\Form\Model\UserInitPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class InitPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("password", PasswordType::class, [
                "attr" => [
                    "class" => "js-password",
                    "placeholder" => "New password",
                ],
                "help" => "8 caractères minimum dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial (? ! * { } [ ]- + = & < > $)",
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
            "data_class" => UserInitPassword::class,
            "translation_domain" => "forms",
        ]);
    }
}
