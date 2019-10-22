<?php

namespace App\Form;

use App\Entity\PersonSearch;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PersonSearchType extends FormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("lastname", null, [
                "attr" => [
                    "class" => "text-uppercase",
                    "placeholder" => "Lastname"
                ]
            ])
            ->add("firstname", null, [
                "attr" => [
                    "class" => "text-capitalize",
                    "placeholder" => "Firstname"
                ]
            ])
            ->add("birthdate", DateType::class, [
                "widget" => "single_text",
                "html5" => false,
                "format" => "dd/MM/yyyy",
                "attr" => [
                    "class" => "w-max-180 js-datepicker",
                    "placeholder" => "jj/mm/aaaa",
                    "autocomplete" => "off"
                ],
                "required" => false
            ])
            ->add("gender", ChoiceType::class, [
                "attr" => [
                    "class" => "col-md-12"
                ],
                "choices" => $this->getchoices(PersonSearch::GENDER),
                "required" => false,
                'placeholder' => "-- Select --"
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
            "id" => "search-person-form",
            "action" => "\list/people",
            "csrf_protection" => false
        ]);
    }

    public function getBlockPrefix()
    {
        return "";
    }
}
