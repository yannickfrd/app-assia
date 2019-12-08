<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Utils\Choices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("firstname")
            ->add("lastname")
            ->add("username")
            ->add("status", ChoiceType::class, [
                "choices" => Choices::getChoices(User::STATUS),
                "label" => "Fonction",
                'placeholder' => "-- Select --",
                "required" => true
            ])->add("email")
            ->add("phone", null, [
                "attr" => [
                    "class" => "js-phone ",
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
