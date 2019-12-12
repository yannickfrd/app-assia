<?php

namespace App\Form\Person;

use App\Entity\Person;

use App\Form\Utils\Choices;;

use App\Entity\PersonSearch;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PersonSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("lastname", null, [
                "label" => false,
                "attr" => [
                    "class" => "w-max-140 text-uppercase",
                    "placeholder" => "Nom",
                    "autocomplete" => "off"
                ]
            ])
            ->add("firstname", null, [
                "label" => false,
                "attr" => [
                    "class" => "w-max-140 text-capitalize",
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
                    "class" => "w-max-180",
                    "placeholder" => "jj/mm/aaaa",
                    "autocomplete" => "off"
                ],
                "required" => false
            ])
            // ->add("age", null, [
            //     "label" => false,
            //     "attr" => [
            //         "placeholder" => "Age",
            //         "class" => "w-max-100",
            //         "autocomplete" => "off"
            //     ]
            // ])
            ->add("gender", ChoiceType::class, [
                'placeholder' => "-- Gender --",
                "label" => false,
                "required" => false,
                "choices" => Choices::getChoices(Person::GENDER),
                "attr" => [
                    "class" => "w-max-120",
                    "autocomplete" => "off"
                ]
            ])
            ->add("phone", null, [
                "label" => false,
                "attr" => [
                    "placeholder" => "Phone",
                    "class" => "js-phone w-max-140",
                    "autocomplete" => "off"
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => PersonSearch::class,
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
