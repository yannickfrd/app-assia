<?php

namespace App\Form;

use App\Entity\GroupPeopleSearch;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class GroupPeopleSearchType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add("lastname", NULL, [
            "label" => false,
            "attr" => [
                "placeholder" => "Nom",
                "class" => "max-w-180",
                "autocomplete" => "off"
            ]
        ])
        ->add("firstname", NULL, [
            "label" => false,
            "attr" => [
                "placeholder" => "Prénom",
                "class" => "max-w-140",
                "autocomplete" => "off"
            ]
        ])
        ->add("birthdate", DateType::class, [
            "label" => false,
            "widget" => "single_text",
            "attr" => [
                "class" => "max-w-180",
                "placeholder" => "jj/mm/aaaa",
                "autocomplete" => "off"
            ],
            "required" => false
        ])

        ->add("head", ChoiceType::class, [
            "placeholder" => "-- DP --",
            "label" => false,
            "required" => false,
            "choices" => $this->getchoices(GroupPeopleSearch::HEAD),
            "attr" => [
                "class" => "max-w-100",
                "autocomplete" => "off"
            ]
        ])
        ->add("familyTypology", ChoiceType::class, [
            "placeholder" => "-- Typologie familiale --",
            "label" => false,
            "required" => false,
            "choices" => $this->getchoices(GroupPeopleSearch::FAMILY_TYPOLOGY),
            "attr" => [
                "class" => "max-w-200",
                "autocomplete" => "off"
            ]
        ])
        ->add("nbPeople", NULL, [
            "label" => false,
            "attr" => [
                "placeholder" => "Nb de pers.",
                "class" => "max-w-100",
                "autocomplete" => "off"
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => GroupPeopleSearch::class,
            "method" => "get",
            "csrf_protection" => false
        ]);
    }

    public function getBlockPrefix() {
        return "";
    }
}
