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
            ->add("lastname", null, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Lastname",
                    "class" => "w-max-180 text-uppercase",
                    "autocomplete" => "off"
                ]
            ])
            ->add("firstname", null, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Firstname",
                    "class" => "w-max-140 text-capitalize",
                    "autocomplete" => "off"
                ]
            ])
            ->add("birthdate", DateType::class, [
                "label" => false,
                "widget" => "single_text",
                "attr" => [
                    "class" => "w-max-180",
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
                    "class" => "w-max-100",
                    "autocomplete" => "off"
                ]
            ])
            ->add("familyTypology", ChoiceType::class, [
                "placeholder" => "-- Family Typology --",
                "label" => false,
                "required" => false,
                "choices" => $this->getchoices(GroupPeopleSearch::FAMILY_TYPOLOGY),
                "attr" => [
                    "class" => "w-max-200",
                    "autocomplete" => "off"
                ]
            ])
            ->add("nbPeople", null, [
                "label" => false,
                "attr" => [
                    "placeholder" => "NbPeople",
                    "class" => "w-max-100",
                    "autocomplete" => "off"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => GroupPeopleSearch::class,
            "method" => "get",
            "translation_domain" => "forms",
            "csrf_protection" => false
        ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}
