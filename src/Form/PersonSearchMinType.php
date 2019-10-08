<?php

namespace App\Form;

use App\Entity\PersonSearch;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PersonSearchMinType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add("lastname", NULL, [
            "label" => false,
            "attr" => [
                "placeholder" => "Nom"
            ]
        ])
        ->add("firstname", NULL, [
            "label" => false,
            "attr" => [
                "placeholder" => "Prénom"
            ]
        ])
        ->add("birthdate", DateType::class, [
            "label" => false,
            "widget" => "single_text",
            "attr" => [
                "class" => "col-md-12"
            ],
            "required" => false
        ])
        ->add("gender", ChoiceType::class, [
            'placeholder' => 'Sexe',
            "label" => false,
            "required" => false,
            "choices" => $this->getchoices(PersonSearch::GENDER),
        ])
        ->add("phone", NULL, [
            "label" => false,
            "attr" => [
                "placeholder" => "Téléphone"
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => PersonSearch::class,
            "translation_domain" => "forms",
        ]);
    }
}
