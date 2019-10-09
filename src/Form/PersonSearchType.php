<?php

namespace App\Form;

use App\Entity\PersonSearch;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PersonSearchType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add("lastname", NULL, [
            "attr" => [
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
            "required" => false,
            "attr" => [
                "class" => "col-md-12"
            ],
            "choices" => $this->getchoices(PersonSearch::GENDER),
        ])
        ->add("phone")
        ->add("email");
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => PersonSearch::class,
            "translation_domain" => "forms",
            "method" => "get",
            "action" => "\list/people",
            "csrf_protection" => false
        ]);
    }

    public function getBlockPrefix() {
        return "";
    }
}
