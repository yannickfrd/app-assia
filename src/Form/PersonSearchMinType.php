<?php

namespace App\Form;

use App\Entity\PersonSearch;

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
                    "class" => "max-w-140 text-uppercase",
                    "placeholder" => "Nom",
                    "autocomplete" => "off"
                ]
            ])
            ->add("firstname", NULL, [
                "label" => false,
                "attr" => [
                    "class" => "max-w-140",
                    "placeholder" => "Prénom",
                    "autocomplete" => "off"
                ]
            ])
            ->add("birthdate", DateType::class, [
                "label" => false,
                "widget" => "single_text",
                // "html5" => false,
                // "format" => "dd/MM/yyyy",
                "attr" => [
                    "class" => "max-w-180",
                    "placeholder" => "jj/mm/aaaa",
                    "autocomplete" => "off"
                ],
                "required" => false
            ])
            // ->add("age", NULL, [
            //     "label" => false,
            //     "attr" => [
            //         "placeholder" => "Age",
            //         "class" => "max-w-100",
            //         "autocomplete" => "off"
            //     ]
            // ])
            ->add("gender", ChoiceType::class, [
                'placeholder' => "-- Sexe --",
                "label" => false,
                "required" => false,
                "choices" => $this->getchoices(PersonSearch::GENDER),
                "attr" => [
                    "class" => "max-w-120",
                    "autocomplete" => "off"
                ]
            ])
            ->add("phone", NULL, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Téléphone",
                    "class" => "max-w-140",
                    "autocomplete" => "off"
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

    public function getBlockPrefix()
    {
        return "";
    }
}
