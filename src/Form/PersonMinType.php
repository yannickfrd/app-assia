<?php

namespace App\Form;

use App\Entity\Person;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PersonMinType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("lastname", NULL, [
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
                    "class" => "max-w-180",
                    "placeholder" => "jj/mm/aaaa",
                    "autocomplete" => "off"
                ],
                "required" => false

            ])
            ->add("gender", ChoiceType::class, [
                "attr" => [
                    "class" => "col-md-12"
                ],
                "choices" => $this->getchoices(Person::GENDER),
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
