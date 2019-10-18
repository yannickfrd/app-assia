<?php

namespace App\Form;

use App\Entity\Person;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PersonType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("lastname", NULL, [
                // "label" => "Nom",
                "attr" => [
                    "class" => "text-uppercase",
                    "placeholder" => "Nom"
                ]
            ])
            ->add("firstname", NULL, [
                "attr" => [
                    "placeholder" => "Prénom"
                ]
            ])
            ->add("birthdate", DateType::class, [
                "widget" => "single_text",
                "attr" => [
                    "class" => "col-md-12"
                ],
                "required" => false

            ])
            ->add("gender", ChoiceType::class, [
                "attr" => [
                    "class" => "col-md-12"
                ],
                "choices" => $this->getchoices(Person::GENDER),
            ])
            ->add("phone1")
            ->add("phone2")
            ->add("email")
            ->add("comment", NULL, [
                "attr" => [
                    "rows" => 5,
                    "placeholder" => "Saisir un commentaire sur la personne"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Person::class,
            "translation_domain" => "forms",
        ]);
    }
}
