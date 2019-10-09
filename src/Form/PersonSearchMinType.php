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
                "placeholder" => "Nom",
                "class" => "max-w-180"
            ]
        ])
        ->add("firstname", NULL, [
            "label" => false,
            "attr" => [
                "placeholder" => "Prénom",
                "class" => "max-w-140"

            ]
        ])
        ->add("birthdate", DateType::class, [
            "label" => false,
            "widget" => "single_text",
            "attr" => [
                "class" => "max-w-180"
            ],
            "required" => false
        ])
        ->add("gender", ChoiceType::class, [
            'placeholder' => 'Sexe',
            "label" => false,
            "required" => false,
            "choices" => $this->getchoices(PersonSearch::GENDER),
            "attr" => [
                "class" => "max-w-120"
            ]
        ])
        ->add("phone", NULL, [
            "label" => false,
            "attr" => [
                "placeholder" => "Téléphone",
                "class" => "max-w-140"
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => PersonSearch::class,
            "method" => "get",
            "csrf_protection" => false
        ]);
    }

    public function getBlockPrefix() {
        return "";
    }
}
